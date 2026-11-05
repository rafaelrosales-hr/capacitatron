import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // habilita el toggle .dark

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                background: 'var(--color-background)',
                text: 'var(--color-text)',
                'text-muted': 'var(--color-text-muted)',
                border: 'var(--color-border)',
                'surface-hover': 'var(--color-surface-hover)',
                accent: 'var(--color-accent)',
            },
            fontFamily: {
                syne: ['Syne Variable', 'sans-serif'],
                poppins: ['Poppins', 'sans-serif'],
                montserrat: ['Montserrat Variable', 'sans-serif'],
                kanit: ['Kanit', 'sans-serif'],
                orbitron: ['Orbitron Variable', 'sans-serif'],
            },
            letterSpacing: {
                3: '0.6em',
                4: '0.7em',
            },
        },
    },

    plugins: [forms],
};