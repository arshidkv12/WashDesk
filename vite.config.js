import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        svelte(),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    //for mobile
    // server: {
    //     host: '0.0.0.0',
    //     port: 5180,
    //     strictPort: true,

    //     cors: true, // ✅ ALLOW CORS

    //     hmr: {
    //     host: '10.140.191.1',
    //     port: 5180,
    //     },
    // },
});
