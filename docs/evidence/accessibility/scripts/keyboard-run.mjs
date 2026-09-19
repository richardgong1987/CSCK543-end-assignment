// Scripted keyboard-only run through the XAMPP site, for the accessibility evidence.
// Only the keyboard is used on the page: Tab, Shift+Tab, Enter, Space, arrow keys and
// typing. Moving to a new address counts as typing it into the address bar.
// At each step: a screenshot showing the focus ring, and the focused element's role and
// name as Chrome's accessibility tree reports them to screen readers.
import { mkdirSync, writeFileSync } from 'node:fs';
import { chromium } from 'playwright-core';

const SITE = 'http://recipebox.localhost';
const SHOTS = '/Users/hanjingong/GolandProjects/CSCK543-end-assignment/docs/images/accessibility/';
const TEXT = new URL('../a11y-evidence/', import.meta.url);
mkdirSync(SHOTS, { recursive: true });
mkdirSync(TEXT, { recursive: true });

const browser = await chromium.launch({ channel: 'chrome', headless: true });
const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
const page = await context.newPage();

const steps = [];
let stepNumber = 0;

async function focusedElement() {
    const focused = page.locator('*:focus');
    if ((await focused.count()) === 0) {
        return '(nothing focused)';
    }
    return (await focused.first().ariaSnapshot()).split('\n')[0].replace(/^- /, '');
}

async function record(slug, action, expectation) {
    stepNumber += 1;
    const file = `keyboard-${String(stepNumber).padStart(2, '0')}-${slug}.png`;
    await page.screenshot({ path: SHOTS + file });
    const focus = await focusedElement();
    steps.push({ step: stepNumber, action, focus, expectation, screenshot: file, url: page.url() });
    console.log(`${stepNumber}. ${action}\n   focus: ${focus}`);
}

/** Presses Tab until the element has focus, and returns how many presses it took. */
async function tabTo(locator, maxPresses = 80) {
    for (let presses = 0; presses <= maxPresses; presses++) {
        if (await locator.evaluate((element) => element === document.activeElement).catch(() => false)) {
            return presses;
        }
        await page.keyboard.press('Tab');
    }
    throw new Error(`Could not reach ${locator} with Tab`);
}

// 1. The skip link is the first thing the keyboard reaches.
await page.goto(SITE + '/', { waitUntil: 'networkidle' });
await page.keyboard.press('Tab');
await record('skip-link', 'Home page, first Tab', 'The "Skip to main content" link appears and has focus');

// 2. Using the skip link moves on to the page's content.
await page.keyboard.press('Enter');
await page.keyboard.press('Tab');
await record('after-skip-link', 'Enter on the skip link, then Tab', 'Focus lands in the main content (the search box), past the navigation');

// 3. Searching.
await page.keyboard.type('pizza');
await record('search-typed', 'Type "pizza" in the search box', 'The search box shows the text and a focus ring');
await page.keyboard.press('Enter');
await page.waitForLoadState('networkidle');

// 4. Reaching the first result.
const firstResult = page.locator('article').first().getByRole('link');
const pressesToResult = await tabTo(firstResult);
await record('first-result', `Tab ${pressesToResult} times to the first result`, 'The whole recipe card is outlined, and its link is named after the recipe');

// 5. Opening it.
await page.keyboard.press('Enter');
await page.waitForLoadState('networkidle');
await record('recipe-page', 'Enter on the result', 'The recipe page opens');

// 6. A guest is offered a way to log in.
await tabTo(page.getByRole('link', { name: 'Log in to save this recipe' }));
await record('guest-login-link', 'Tab to "Log in to save this recipe"', 'The link has a focus ring');
await page.keyboard.press('Enter');
await page.waitForLoadState('networkidle');
await record('login-autofocus', 'Enter on the link', 'The login page opens with the email field already focused');

// 7. Logging in.
await page.keyboard.type('amelia@example.test');
await page.keyboard.press('Tab');
await page.keyboard.type('password');
await record('login-filled', 'Type the email, Tab, type the password', 'The password field has focus');
await page.keyboard.press('Enter');
await page.waitForURL('**/dashboard');
await page.waitForLoadState('networkidle');
await record('account-page', 'Enter to log in', 'The account page opens');

// 8. Saving a favourite keeps focus on the button.
await page.goto(SITE + '/recipes/couscous-salad', { waitUntil: 'networkidle' });
const favouriteButton = page.getByRole('button', { name: /(Save|Remove) favourite/ });
const labelBefore = (await favouriteButton.textContent()).trim();
await tabTo(favouriteButton);
await record('favourite-focused', `Tab to "${labelBefore}"`, 'The button has a focus ring');
await page.keyboard.press('Enter');
await page.getByRole('status').filter({ hasText: /Recipe (saved to|removed from) favourites\./ }).waitFor();
await record('favourite-toggled', 'Enter on the button', 'The label flips, focus stays on the same button, and the confirmation is announced as a status message');

// 9. Rating with the arrow keys.
const overall = page.getByRole('group', { name: /Overall/ });
const firstOverallRadio = overall.getByRole('radio').first();
const checkedBefore = await overall.locator('input:checked').count();
// Tab enters a radio group at its checked option, or at the first one if none is checked.
await tabTo(checkedBefore > 0 ? overall.locator('input:checked') : firstOverallRadio);
if (checkedBefore === 0) {
    await page.keyboard.press('Space');
}
// Arrow keys wrap around a radio group, so step right from the current star to reach 4.
const currentScore = Number(await overall.locator('input:checked').inputValue());
for (let i = 0; i < (4 - currentScore + 5) % 5; i++) {
    await page.keyboard.press('ArrowRight');
}
await record('rating-arrows', 'Tab into the Overall stars, arrow keys to 4', 'Four stars fill, and the focused radio is announced as "4 out of 5", checked');

await tabTo(page.getByRole('button', { name: /(Save|Update) rating/ }));
await page.keyboard.press('Enter');
await page.getByRole('status').filter({ hasText: 'Your rating has been saved.' }).waitFor();
await record('rating-saved', 'Tab to the rating button, Enter', 'The page reloads with "Your rating has been saved."');

// 10. Logging out.
await tabTo(page.getByRole('button', { name: 'Log out' }));
await record('logout-focused', 'Tab to "Log out"', 'The button has a focus ring');
await page.keyboard.press('Enter');
await page.waitForLoadState('networkidle');

// 11. Form errors: submitting an empty registration form.
await page.goto(SITE + '/register', { waitUntil: 'networkidle' });
await page.keyboard.press('Enter');
await record('register-errors', 'Registration page, Enter on the empty form', 'Every error shows, and focus moves to the first invalid field (Name), which is linked to its message');

const describedBy = await page.locator('#name').getAttribute('aria-describedby');
const invalid = await page.locator('#name').getAttribute('aria-invalid');
steps[steps.length - 1].note = `Name field: aria-invalid="${invalid}", aria-describedby="${describedBy}"`;

// The accessibility tree of the main pages: what a screen reader is given.
const trees = {};
const signIn = async () => {
    await page.goto(SITE + '/login', { waitUntil: 'networkidle' });
    await page.fill('#email', 'amelia@example.test');
    await page.fill('#password', 'password');
    await Promise.all([page.waitForURL('**/dashboard'), page.press('#password', 'Enter')]);
};
for (const [name, path] of [['home', '/'], ['recipes', '/recipes?q=pizza'], ['recipe-guest', '/recipes/healthy-pizza'], ['login', '/login'], ['register', '/register'], ['privacy', '/privacy']]) {
    await page.goto(SITE + path, { waitUntil: 'networkidle' });
    trees[name] = await page.locator('body').ariaSnapshot();
}
await signIn();
for (const [name, path] of [['account', '/dashboard'], ['recipe-signed-in', '/recipes/healthy-pizza'], ['account-settings', '/account']]) {
    await page.goto(SITE + path, { waitUntil: 'networkidle' });
    trees[name] = await page.locator('body').ariaSnapshot();
}

await browser.close();

writeFileSync(new URL('keyboard-steps.json', TEXT), JSON.stringify(steps, null, 2));
for (const [name, tree] of Object.entries(trees)) {
    writeFileSync(new URL(`tree-${name}.yaml`, TEXT), tree + '\n');
}
console.log(`\n${steps.length} steps, ${Object.keys(trees).length} accessibility trees saved`);
