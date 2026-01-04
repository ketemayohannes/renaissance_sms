import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: ['resources/views/**/*.blade.php'], // Only watch blade files, not full refresh
        }),
    ],
    server: {
        host: 'localhost',
        hmr: {
            host: 'localhost',
        },
        watch: {
            // Exclude directories that don't need watching
            ignored: ['**/node_modules/**', '**/storage/**', '**/vendor/**', '**/.git/**'],
        },
    },
});
