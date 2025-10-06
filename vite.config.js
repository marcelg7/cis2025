import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/app2.css', // Add this line
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    publicDir: false, // Disable default public dir copying
    resolve: {
        alias: {
            'tinymce': path.resolve(__dirname, 'node_modules/tinymce'),
        },
    },
    build: {
        rollupOptions: {
            external: [],
            output: {
                assetFileNames: 'assets/[name].[ext]',
            },
        },
    },
});