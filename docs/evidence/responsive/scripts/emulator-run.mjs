// Real Android Chrome in an emulator: open each page over Wi-Fi at the Mac's address,
// measure horizontal overflow inside the device's Chrome, and capture the whole device
// screen (status bar and Chrome's address bar included) with adb.
//
// Usage: node emulator-run.mjs <label> <adb-serial> [portrait|landscape]
import { execFileSync } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
import { chromium } from 'playwright-core';

const [label, serial, orientation = 'portrait'] = process.argv.slice(2);
const ADB = '/Users/hanjingong/Library/Android/sdk/platform-tools/adb';
const BASE = 'http://192.168.2.100';
const SHOTS = '/Users/hanjingong/GolandProjects/CSCK543-end-assignment/docs/images/responsive/';
const TEXT = new URL('../responsive-evidence/', import.meta.url);
mkdirSync(SHOTS, { recursive: true });
mkdirSync(TEXT, { recursive: true });

// A tablet screenshot is several megabytes, well over execFileSync's 1 MB default.
const adb = (...args) => execFileSync(ADB, ['-s', serial, ...args], { maxBuffer: 64 * 1024 * 1024 });
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Rotation: fix the screen to portrait (0) or landscape (1).
adb('shell', 'settings', 'put', 'system', 'accelerometer_rotation', '0');
adb('shell', 'settings', 'put', 'system', 'user_rotation', orientation === 'landscape' ? '1' : '0');
await sleep(2000);

// 9222 is often taken on a developer's Mac, so the forward uses 9333.
adb('forward', 'tcp:9333', 'localabstract:chrome_devtools_remote');
const browser = await chromium.connectOverCDP('http://localhost:9333');
const context = browser.contexts()[0];
const page = context.pages()[0];

// Android and Chrome overlays that have nothing to do with the site but would cover it in
// a screenshot: the one-off stylus tutorial, Password Manager's breach warning for the
// sample password ("password"), and the offer to save a password.
const SYSTEM_OVERLAYS = [
    { shows: 'Try out your stylus', dismissWith: ['Cancel'] },
    { shows: 'Change your password', dismissWith: ['OK'] },
    { shows: 'Save password', dismissWith: ['Never', 'No thanks', 'Not now'] },
];

function screenNodes() {
    adb('shell', 'uiautomator', 'dump', '/sdcard/ui.xml');
    return adb('shell', 'cat', '/sdcard/ui.xml').toString().split('<node').slice(1);
}

async function dismissSystemOverlays() {
    for (let round = 0; round < 4; round++) {
        const nodes = screenNodes();
        const overlay = SYSTEM_OVERLAYS.find(({ shows }) => nodes.some((node) => node.includes(`text="${shows}`)));
        if (!overlay) {
            return;
        }
        const button = nodes.find((node) => overlay.dismissWith.some((label) => node.includes(`text="${label}"`)));
        const [x1, y1, x2, y2] = (button?.match(/bounds="\[(\d+),(\d+)\]\[(\d+),(\d+)\]"/) ?? []).slice(1).map(Number);
        if (!button || Number.isNaN(x1)) {
            console.log(`  could not dismiss "${overlay.shows}"`);
            return;
        }
        adb('shell', 'input', 'tap', String(Math.round((x1 + x2) / 2)), String(Math.round((y1 + y2) / 2)));
        console.log(`  dismissed system overlay "${overlay.shows}"`);
        await sleep(1200);
    }
}

async function measure() {
    return page.evaluate(() => {
        const viewportWidth = document.documentElement.clientWidth;
        const scrolls = (el) => ['auto', 'scroll'].includes(getComputedStyle(el).overflowX);
        const insideScroller = (el) => {
            for (let node = el.parentElement; node && node !== document.body; node = node.parentElement) {
                if (scrolls(node)) return true;
            }
            return false;
        };
        const offenders = [...document.body.querySelectorAll('*')]
            .filter((el) => {
                const rect = el.getBoundingClientRect();
                return rect.width > 0 && rect.right > viewportWidth + 1 && !insideScroller(el) && !el.closest('.sr-only');
            })
            .slice(0, 5)
            .map((el) => `${el.tagName.toLowerCase()}.${[...el.classList].slice(0, 3).join('.')}`);
        return {
            cssWidth: viewportWidth,
            pageWidth: document.documentElement.scrollWidth,
            devicePixelRatio: window.devicePixelRatio,
            horizontalOverflowPx: document.documentElement.scrollWidth - viewportWidth,
            offenders,
            userAgent: navigator.userAgent,
        };
    });
}

const results = [];
const suffix = orientation === 'landscape' ? '-landscape' : '';

async function capture(slug, path, prepare) {
    await page.goto(BASE + path, { waitUntil: 'networkidle' });
    await page.evaluate(() => window.scrollTo(0, 0));
    if (prepare) {
        await prepare();
    }
    await sleep(1200);
    await dismissSystemOverlays();
    const metrics = await measure();
    const file = `${label}${suffix}-${slug}.png`;
    writeFileSync(SHOTS + file, adb('exec-out', 'screencap', '-p'));
    results.push({ device: label, orientation, page: path, screenshot: file, ...metrics });
    console.log(`${file.padEnd(46)} ${metrics.cssWidth}px wide, overflow ${metrics.horizontalOverflowPx}px${metrics.offenders.length ? ' ← ' + metrics.offenders.join(', ') : ''}`);
}

try {
    await capture('home', '/');
    await capture('search-results', '/recipes?q=a');
    await capture('recipe', '/recipes/healthy-pizza');
    await capture('login', '/login');
    await capture('register-errors', '/register', async () => {
        await page.click('button[type="submit"]');
        await sleep(800);
        // The form moves focus to the first invalid field, which opens the on-screen
        // keyboard (and, the first time, its stylus tutorial) over the page. Taking focus
        // away closes the keyboard, so the screenshot shows the error messages themselves.
        await page.evaluate(() => document.activeElement?.blur());
        await sleep(1500);
    });
    await capture('privacy', '/privacy');

    await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
    await page.fill('#email', 'amelia@example.test');
    await page.fill('#password', 'password');
    await Promise.all([page.waitForURL('**/dashboard'), page.click('button[type="submit"]')]);
    await sleep(2000);
    await dismissSystemOverlays();
    await capture('account', '/dashboard');
    await capture('account-settings', '/account');
    await capture('rating-form', '/recipes/healthy-pizza', async () => {
        await page.locator('#rating-heading').scrollIntoViewIfNeeded();
    });

    // Log out again, so the next run starts as a guest.
    await page.goto(BASE + '/dashboard', { waitUntil: 'networkidle' });
    await page.click('form[action$="/logout"] button');
    await sleep(1000);
} finally {
    writeFileSync(new URL(`${label}${suffix}.json`, TEXT), JSON.stringify(results, null, 2));
    await browser.close().catch(() => {});
}
