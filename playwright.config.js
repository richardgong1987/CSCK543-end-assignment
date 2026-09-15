import { fileURLToPath } from 'node:url';
import { defineConfig } from '@playwright/test';

const PORT = 8123;
const E2E_DATABASE = fileURLToPath(new URL('./database/e2e.sqlite', import.meta.url));

export default defineConfig({
    testDir: './tests/e2e',
    // One PHP server and one SQLite file are shared by every test, so run them one at a time.
    workers: 1,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? [['list'], ['html', { open: 'never' }]] : 'list',
    use: {
        baseURL: `http://127.0.0.1:${PORT}`,
        // The brief's target browser: the installed stable Chrome, not Playwright's Chromium build.
        channel: 'chrome',
        trace: 'retain-on-failure',
    },
    webServer: {
        // The e2e run gets its own throwaway SQLite database, rebuilt every time, so it never
        // touches a developer's MySQL data. `--database=sqlite` is deliberate: if the
        // environment below were ever lost, migrate:fresh still could not reach MySQL.
        //
        // `php -S` with Laravel's router is used instead of `php artisan serve`, because
        // artisan serve starts its PHP process without these environment variables and would
        // quietly fall back to the settings in .env. The router loads index.php from the
        // working directory, so the server must start inside public/.
        command: [
            `php -r "touch('database/e2e.sqlite');"`,
            'php artisan migrate:fresh --seed --force --database=sqlite',
            `cd public && php -S 127.0.0.1:${PORT} ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`,
        ].join(' && '),
        url: `http://127.0.0.1:${PORT}/up`,
        reuseExistingServer: false,
        timeout: 120_000,
        env: {
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: E2E_DATABASE,
            MAIL_MAILER: 'array',
            // Serve public/build even if a Vite dev server has written public/hot.
            VITE_HOT_FILE: 'storage/framework/e2e-has-no-hot-file',
        },
    },
});
