// Drives the real macOS VoiceOver through the XAMPP site with AppleScript, and records what
// VoiceOver says after every action. Keys go through System Events, at operating-system
// level, exactly as a user's keyboard would send them; VoiceOver keeps the user's own
// settings.
import { execFileSync } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
import { chromium } from 'playwright-core';

const SITE = 'http://recipebox.localhost';
const SHOTS = '/Users/hanjingong/GolandProjects/CSCK543-end-assignment/docs/images/accessibility/';
const LOGS = new URL('../a11y-evidence/voiceover/', import.meta.url);
mkdirSync(LOGS, { recursive: true });

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const osa = (script) => execFileSync('osascript', ['-e', script]).toString().trim();

const VO = 'control down, option down';
const KEY = {
    next: `key code 124 using {${VO}}`,
    nextHeading: `keystroke "h" using {${VO}, command down}`,
    tab: 'key code 48',
    enter: 'key code 36',
    right: 'key code 124',
    space: 'key code 49',
};

function lastPhrase() {
    try {
        return osa('tell application "VoiceOver" to return content of last phrase');
    } catch {
        return '';
    }
}

/** Sends one key (or keystroke) to the front app, then collects what VoiceOver says. */
async function press(action, label, listenMs = 1600) {
    const before = lastPhrase();
    osa(`tell application "System Events" to ${action}`);
    const heard = [];
    const deadline = Date.now() + listenMs;
    while (Date.now() < deadline) {
        await sleep(200);
        const phrase = lastPhrase();
        if (phrase && phrase !== before && !heard.includes(phrase)) {
            heard.push(phrase);
        }
    }
    current.push({ key: label, heard });
    console.log(`  [${label}] ${heard.join('  |  ') || '(nothing new)'}`);
}

async function typeText(text) {
    osa(`tell application "System Events" to keystroke ${JSON.stringify(text)}`);
    await sleep(800);
    current.push({ key: `type "${text.replace(/password/, '********')}"`, heard: [] });
}

const server = await chromium.launchServer({
    channel: 'chrome',
    headless: false,
    args: ['--force-renderer-accessibility', '--window-position=0,0', '--window-size=1280,900'],
});
const chromePid = server.process().pid;
const browser = await chromium.connect(server.wsEndpoint());
const context = await browser.newContext({ viewport: null });
const page = await context.newPage();

function bringTestChromeToFront() {
    osa(`tell application "System Events" to set frontmost of (first process whose unix id is ${chromePid}) to true`);
}

async function openPage(path) {
    await page.goto(SITE + path, { waitUntil: 'networkidle' });
    bringTestChromeToFront();
    // Test setup, not part of what is tested: a click on empty space beside the content puts
    // keyboard focus in the page rather than Chrome's toolbar, as it would be for a user
    // who has just followed a link.
    await page.mouse.click(1250, 700);
    await sleep(1500);
}

/** Test setup: sign in without going through the keyboard, so later scenarios start signed in. */
async function signInForSetup() {
    await page.goto(SITE + '/login', { waitUntil: 'networkidle' });
    await page.fill('#email', 'amelia@example.test');
    await page.fill('#password', 'password');
    await Promise.all([page.waitForURL('**/dashboard'), page.press('#password', 'Enter')]);
}

const results = [];
let current = [];

async function scenario(slug, title, run) {
    current = [];
    console.log(`\n=== ${title}`);
    let error = null;
    try {
        await run();
    } catch (caught) {
        error = caught.message;
        console.log(`  ERROR: ${error}`);
    }
    const file = `voiceover-${slug}.png`;
    await page.screenshot({ path: SHOTS + file });
    results.push({ slug, title, steps: current, error, screenshot: file });
    const lines = current.map((step) => `${step.key}\n${step.heard.map((phrase) => `    VoiceOver: "${phrase}"`).join('\n') || '    (VoiceOver said nothing new)'}`);
    writeFileSync(new URL(`${slug}.txt`, LOGS), `${title}\n\n${lines.join('\n')}${error ? `\n\nERROR: ${error}` : ''}\n`);
}

try {
    // Skip VoiceOver's welcome dialog, which would otherwise take focus on its first start.
    execFileSync('defaults', ['write', 'com.apple.VoiceOverTraining', 'doNotShowSplashScreen', '-bool', 'true']);

    await openPage('/');
    execFileSync('open', ['/System/Library/CoreServices/VoiceOver.app']);
    for (let i = 0; i < 40 && !lastPhrase(); i++) {
        await sleep(250);
    }
    await sleep(2500);
    bringTestChromeToFront();
    await sleep(1500);

    await scenario('home-reading-order', 'Home page: first Tab, then VoiceOver "next item" (Control-Option-Right)', async () => {
        await press(KEY.tab, 'Tab');
        for (let i = 0; i < 8; i++) {
            await press(KEY.next, 'VO-Right');
        }
    });

    await scenario('home-headings', 'Home page: VoiceOver "next heading" (Control-Option-Command-H)', async () => {
        await openPage('/');
        await press(KEY.tab, 'Tab');
        for (let i = 0; i < 7; i++) {
            await press(KEY.nextHeading, 'VO-Cmd-H');
        }
    });

    await scenario('recipe-headings', 'Recipe page (guest): VoiceOver "next heading"', async () => {
        await openPage('/recipes/healthy-pizza');
        await press(KEY.tab, 'Tab');
        for (let i = 0; i < 8; i++) {
            await press(KEY.nextHeading, 'VO-Cmd-H');
        }
    });

    await scenario('login-empty', 'Login: Enter on the empty form, with focus in the email field', async () => {
        await openPage('/login');
        await page.focus('#email');
        await press(KEY.enter, 'Enter', 3000);
    });

    await scenario('login-invalid-email', 'Login: an email without a domain, then Enter', async () => {
        await openPage('/login');
        await page.focus('#email');
        await typeText('amelia');
        await press(KEY.enter, 'Enter', 3000);
    });

    await signInForSetup();

    await scenario('favourite', 'Recipe page (signed in): Tab to the favourite button, Enter', async () => {
        await openPage('/recipes/mango-pie');
        const button = page.getByRole('button', { name: /(Save|Remove) favourite/ });
        for (let presses = 0; presses < 40; presses++) {
            if (await button.evaluate((element) => element === document.activeElement)) {
                break;
            }
            await press(KEY.tab, 'Tab', 600);
        }
        current.length = 0;
        await press(KEY.enter, 'Enter on the focused favourite button', 3500);
    });

    await scenario('rating', 'Recipe page (signed in): Tab into the Overall stars, Right arrow twice', async () => {
        const overall = page.getByRole('group', { name: /Overall/ });
        for (let presses = 0; presses < 60; presses++) {
            if (await overall.evaluate((group) => group.contains(document.activeElement))) {
                break;
            }
            await press(KEY.tab, 'Tab', 600);
        }
        current.splice(0, current.length - 1);
        await press(KEY.right, 'Right arrow', 2000);
        await press(KEY.right, 'Right arrow', 2000);
    });
} finally {
    // Never leave VoiceOver on: ask it to quit, and if it is still running, stop it.
    try {
        osa('tell application "VoiceOver" to quit');
    } catch {
        // Not running, or it refused.
    }
    await sleep(1500);
    try {
        execFileSync('pkill', ['-x', 'VoiceOver']);
    } catch {
        // Nothing left to stop.
    }
    await browser.close().catch(() => {});
    await server.close().catch(() => {});
    writeFileSync(new URL('all.json', LOGS), JSON.stringify(results, null, 2));
}
