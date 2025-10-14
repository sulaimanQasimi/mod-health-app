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
                'public/assets/js/vue/lab-app.js',
                'public/assets/js/vue/prescription-app.js',
                'public/assets/js/vue/diagnosis-app.js',
                'public/assets/js/vue/advice-app.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js'
        }
    }
});
