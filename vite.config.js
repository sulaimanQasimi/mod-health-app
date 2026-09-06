import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inertia from '@inertiajs/vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import flowbiteReact from "flowbite-react/plugin/vite";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    plugins: [
        tailwindcss(),
        react({
            jsxRuntime: 'automatic',
            jsxImportSource: 'react',
        }),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.tsx',
                'resources/js/app.js',
                'resources/js/ckeditor.js',
                'public/assets/js/qr-code-generator.js',
                'public/assets/js/simple-qr-fallback.js',
            ],
            refresh: true,
        }),
        inertia(),
        flowbiteReact()
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            'ckeditor5/ckeditor5.css': path.resolve(__dirname, 'node_modules/ckeditor5/dist/ckeditor5.css'),
        }
    },
    build: {
        rollupOptions: {
            // Bundle all dependencies, including moment-jalaali
        }
    }
});
