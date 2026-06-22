import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inertia from '@inertiajs/vite';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
import vue from '@vitejs/plugin-vue';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import flowbiteReact from "flowbite-react/plugin/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        vue({
            include: /\.vue$/,
            exclude: /\.(jsx|tsx)$/,
        }),
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
                'public/assets/js/vue/lab-section.js',
                'public/assets/js/vue/lab-test-registration-section.js',
                'public/assets/js/vue/dentist-registration-section.js',
                'public/assets/js/vue/nephrology-registration-section.js',
                'public/assets/js/vue/dental-chart-app.js',
                'public/assets/js/vue/dental-chart-advanced-app.js',
                'public/assets/js/vue/prescription-app.js',
                'public/assets/js/vue/prescription-show-app.js',
                'public/assets/js/vue/prescription-index-app.js',
                'public/assets/js/vue/diagnosis-app.js',
                'public/assets/js/vue/advice-app.js',
                'public/assets/js/vue/consultation-app.js',
                'public/assets/js/vue/icu-consultation-app.js',
                'public/assets/js/vue/visit-app.js',
                'public/assets/js/vue/appointment-prescription-app.js',
                'public/assets/js/vue/appointment-advice-app.js',
                'public/assets/js/vue/nursing-note-app.js',
                'public/assets/js/qr-code-generator.js',
                'public/assets/js/simple-qr-fallback.js'
            ],
            refresh: true,
        }),
        inertia(),
        flowbiteReact()
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
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