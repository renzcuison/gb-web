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
    safelist: [
        {
            pattern: /slide-up-(enter|leave)-(active|from|to)/,
        },
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#f0f9fa',
                    100: '#e0f2f5',
                    200: '#b8e5ed',
                    300: '#7ed2e1',
                    400: '#4fb9cd',
                    500: '#389db0', 
                    600: '#2b7e91',
                    700: '#286878',
                    800: '#265664',
                    900: '#234854',
                    950: '#132f38',
                },
            },
            fontFamily: {
                sans: ['"Inter"', ...defaultTheme.fontFamily.sans],
                display: ['"Public Sans"', 'sans-serif'],
                logo: ['"Libre Caslon Display"', 'serif'],
            },
            letterSpacing: {
                tightest: '-.03em',
            }
        },
    },

    plugins: [forms],
};