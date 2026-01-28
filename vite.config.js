import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(), // ✅ enables .vue file support
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        host: '0.0.0.0', // Allow connections from any IP
        allowedHosts: true, // ✅ Allow ngrok URLs
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
            clientPort: process.env.VITE_HMR_HOST ? 443 : 5173,
        },
    },
});
