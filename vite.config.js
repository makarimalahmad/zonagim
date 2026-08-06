import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    server: {
        warmup: {
            clientFiles: [
                'resources/js/app.jsx',
                'resources/js/Pages/Landing.jsx',
                'resources/js/Pages/Market/Index.jsx',
                'resources/css/app.css',
            ],
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/js/app.jsx',
                'resources/css/filament/admin/theme.css',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
});
