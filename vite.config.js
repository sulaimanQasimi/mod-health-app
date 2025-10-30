import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/ckeditor.js',
                'public/assets/js/vue/lab-section.js',
                'public/assets/js/vue/lab-test-registration-section.js',
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
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js'
        }
    },
    build: {
        rollupOptions: {
            // Bundle all dependencies, including moment-jalaali
        }
    }
});
