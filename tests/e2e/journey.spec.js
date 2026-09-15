import { expect, test } from '@playwright/test';

// The journey from proposal §7.1: register, log in, search, open a recipe, save it as a
// favourite, find it on the account page, log out.

const PASSWORD = 'e2e-password-123';

function uniqueEmail() {
    return `e2e-${Date.now()}-${Math.random().toString(36).slice(2, 8)}@example.test`;
}

test('a visitor registers, finds a recipe, saves it and sees it on their account page', async ({ page }) => {
    const email = uniqueEmail();
    let recipeTitle;

    await test.step('register', async () => {
        await page.goto('/register');
        await page.getByLabel('Name', { exact: true }).fill('E2E Visitor');
        await page.getByLabel('Email address', { exact: true }).fill(email);
        await page.getByLabel('Password', { exact: true }).fill(PASSWORD);
        await page.getByLabel('Confirm password', { exact: true }).fill(PASSWORD);
        await page.getByRole('button', { name: 'Create account' }).click();

        await expect(page).toHaveURL(/\/dashboard$/);
        await expect(page.getByRole('heading', { level: 1 })).toHaveText('Your account');
    });

    await test.step('log out, then log back in', async () => {
        await page.getByRole('button', { name: 'Log out' }).click();
        await page.getByRole('link', { name: 'Log in', exact: true }).click();

        await page.getByLabel('Email address', { exact: true }).fill(email);
        await page.getByLabel('Password', { exact: true }).fill(PASSWORD);
        await page.getByRole('button', { name: 'Log in' }).click();

        await expect(page).toHaveURL(/\/dashboard$/);
    });

    await test.step('search for a recipe', async () => {
        await page.getByRole('link', { name: 'Recipes', exact: true }).click();
        await page.getByLabel('Search recipes').fill('pizza');
        await page.getByLabel('Search recipes').press('Enter');

        await expect(page).toHaveURL(/[?&]q=pizza/);
        await expect(page.locator('article').first()).toContainText(/pizza/i);
    });

    await test.step('open the recipe', async () => {
        const firstResult = page.locator('article').first();
        recipeTitle = (await firstResult.getByRole('heading').textContent()).trim();

        // Clicking the card itself, not the title: the whole card is the link.
        await firstResult.click();

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(recipeTitle);
    });

    await test.step('save it as a favourite without a page reload', async () => {
        const urlBefore = page.url();
        await page.getByRole('button', { name: 'Save favourite' }).click();

        await expect(page.getByRole('button', { name: 'Remove favourite' })).toBeVisible();
        await expect(page.getByRole('status').filter({ hasText: 'Recipe saved to favourites.' })).toBeVisible();
        expect(page.url()).toBe(urlBefore);
    });

    await test.step('find it on the account page', async () => {
        await page.getByRole('link', { name: 'Your account' }).click();

        const savedRecipes = page.getByRole('region', { name: 'Saved recipes' });
        await expect(savedRecipes.getByRole('heading', { name: recipeTitle })).toBeVisible();
    });

    await test.step('log out', async () => {
        await page.getByRole('button', { name: 'Log out' }).click();

        await expect(page.getByRole('link', { name: 'Log in', exact: true })).toBeVisible();
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login$/);
    });
});

test('a signed-in user rates a recipe with the star control and sees it on their account page', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Email address', { exact: true }).fill('amelia@example.test');
    await page.getByLabel('Password', { exact: true }).fill('password');
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/dashboard$/);

    await page.goto('/recipes/healthy-pizza');
    const overall = page.getByRole('group', { name: /Overall/ });
    await overall.getByText('4 out of 5').click({ force: true });
    await expect(overall.locator('input:checked')).toHaveValue('4');

    await page.getByRole('button', { name: /(Save|Update) rating/ }).click();

    await expect(page.getByRole('status').filter({ hasText: 'Your rating has been saved.' })).toBeVisible();
    await expect(overall.locator('input:checked')).toHaveValue('4');

    await page.getByRole('link', { name: 'Your account' }).click();
    const ratings = page.getByRole('region', { name: 'Your ratings' });
    await expect(ratings.getByRole('listitem').filter({ hasText: 'Healthy pizza' })).toContainText(/Overall:\s*4 out of 5/);
});
