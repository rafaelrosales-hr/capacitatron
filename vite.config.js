import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fontsource } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            fonts: [
                fontsource('Syne', { alias: 'syne' }),
                fontsource('Poppins', { alias: 'poppins' }),
                fontsource('Montserrat', { alias: 'montserrat', weights: [400, 500, 600, 700] }),
                fontsource('Orbitron', { alias: 'orbitron', weights: [400, 500, 600, 700, 800, 900] }),
            ],
            refresh: true,
        }),
    ],
});