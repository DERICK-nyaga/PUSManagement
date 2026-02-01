import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/employee-statuses.js',
                'resources/js/employee-balance.js',
                'resources/js/deductions.js',
                'resources/js/payments.js',
                'resources/js/airtime.js'
            ],
            refresh: true,
        }),
    ],
});
