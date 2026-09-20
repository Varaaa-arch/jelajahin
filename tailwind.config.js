import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: {
                    DEFAULT: '#0d1117',
                    mid: '#161b22',
                    light: '#1e2530',
                },
                teal: {
                    DEFAULT: '#0ea5a0',
                    dark: '#0c8f8a',
                    light: '#14c4be',
                },
            },
            backgroundImage: {
                'hero-gradient': 'linear-gradient(to bottom, rgba(13,17,23,0.35) 0%, rgba(13,17,23,0.2) 40%, rgba(13,17,23,0.65) 80%, rgba(13,17,23,0.85) 100%)',
            },
        },
    },

    plugins: [forms],
};
