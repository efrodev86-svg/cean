import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Montserrat', 'Figtree', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                cean: {
                    cyan: '#59C1E3',
                    navy: '#2C5EAB',
                    'navy-hover': '#245099',
                    orange: '#F18F35',
                    red: '#C82D31',
                    soft: '#E8F6FB',
                },
            },
            backgroundImage: {
                'cean-dots': 'radial-gradient(circle, rgba(255,255,255,0.07) 1px, transparent 1px)',
            },
            backgroundSize: {
                'cean-dots': '22px 22px',
            },
        },
    },

    plugins: [forms],
};
