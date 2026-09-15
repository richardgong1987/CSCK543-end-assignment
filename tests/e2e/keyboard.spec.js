import { expect, test } from '@playwright/test';

// Proposal §7.1 asks for keyboard-only interaction. Every step here uses only Tab, arrow
// keys, typing and Enter: no clicks.

/**
 * Presses Tab until the element is focused, the way a keyboard user moves through a page.
 * Fails if the element cannot be reached, which is exactly the bug this should catch.
 */
async function tabTo(page, locator, maxPresses = 80) {
    for (let press = 0; press < maxPresses; press++) {
        if (await locator.evaluate((element) => element === document.activeElement).catch(() => false)) {
            return;
        }
        await page.keyboard.press('Tab');
    }

    throw new Error(`Could not reach ${locator} with ${maxPresses} presses of Tab`);
}

test('the first Tab on a page reaches the skip link', async ({ page }) => {
    await page.goto('/recipes');
    await page.keyboard.press('Tab');

    await expect(page.getByRole('link', { name: 'Skip to main content' })).toBeFocused();
});

test('a keyboard-only user registers, searches, saves and rates a recipe, and logs out', async ({ page }) => {
    const email = `keyboard-${Date.now()}@example.test`;

    await test.step('register', async () => {
        await page.goto('/register');

        // The name field has autofocus.
        await expect(page.getByLabel('Name', { exact: true })).toBeFocused();
        await page.keyboard.type('Keyboard User');
        await tabTo(page, page.getByLabel('Email address', { exact: true }));
        await page.keyboard.type(email);
        await tabTo(page, page.getByLabel('Password', { exact: true }));
        await page.keyboard.type('keyboard-password-123');
        await tabTo(page, page.getByLabel('Confirm password', { exact: true }));
        await page.keyboard.type('keyboard-password-123');
        await page.keyboard.press('Enter');

        await expect(page).toHaveURL(/\/dashboard$/);
    });

    await test.step('search', async () => {
        await tabTo(page, page.getByRole('link', { name: 'Recipes', exact: true }));
        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(/\/recipes$/);

        await tabTo(page, page.getByLabel('Search recipes'));
        await page.keyboard.type('salad');
        await page.keyboard.press('Enter');

        await expect(page).toHaveURL(/[?&]q=salad/);
    });

    await test.step('open the first result', async () => {
        const firstResultLink = page.locator('article').first().getByRole('link');
        const title = (await firstResultLink.textContent()).trim();

        await tabTo(page, firstResultLink);
        await page.keyboard.press('Enter');

        await expect(page.getByRole('heading', { level: 1 })).toHaveText(title);
    });

    await test.step('save it as a favourite', async () => {
        await tabTo(page, page.getByRole('button', { name: 'Save favourite' }));
        await page.keyboard.press('Enter');

        await expect(page.getByRole('button', { name: 'Remove favourite' })).toBeFocused();
        await expect(page.getByRole('status').filter({ hasText: 'Recipe saved to favourites.' })).toBeVisible();
    });

    await test.step('rate it 4 out of 5 with the arrow keys', async () => {
        const overall = page.getByRole('group', { name: /Overall/ });

        // Tabbing into a radio group with nothing chosen lands on its first option.
        await tabTo(page, overall.getByRole('radio').first());
        await page.keyboard.press('Space');
        await page.keyboard.press('ArrowRight');
        await page.keyboard.press('ArrowRight');
        await page.keyboard.press('ArrowRight');
        await expect(overall.locator('input:checked')).toHaveValue('4');

        await tabTo(page, page.getByRole('button', { name: 'Save rating' }));
        await page.keyboard.press('Enter');

        await expect(page.getByRole('status').filter({ hasText: 'Your rating has been saved.' })).toBeVisible();
    });

    await test.step('log out', async () => {
        await tabTo(page, page.getByRole('button', { name: 'Log out' }));
        await page.keyboard.press('Enter');

        await expect(page.getByRole('link', { name: 'Log in', exact: true })).toBeVisible();
    });
});
