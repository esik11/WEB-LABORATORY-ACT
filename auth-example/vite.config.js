import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss', // Your main SCSS file
                'resources/js/app.js',  
                'resources/css/app.css'    // Your main JS file
            ],
            refresh: true,
        }),
    ],
});