import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Read from the @fontsource/instrument-sans package in node_modules, so a build
                // never downloads fonts: a download that timed out used to fail CI at random.
                // Only the WOFF2 files: the fontsource() provider also adds a WOFF copy of each
                // weight, which browsers download as well (docs/performance.md, PC9).
                local('Instrument Sans', {
                    variants: [400, 500, 600].map((weight) => ({
                        src: `node_modules/@fontsource/instrument-sans/files/instrument-sans-latin-${weight}-normal.woff2`,
                        weight,
                    })),
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
