import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/dashboard-refresh.css', 'resources/css/module-refresh.css', 'resources/css/workspace-v2.css', 'resources/css/workspace-v3.css', 'resources/css/studio-ui.css', 'resources/css/coachpro-blue.css', 'resources/css/coachpro-frame.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
