import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        // Signature page animations
        'signature-page-container',
        'signature-card',
        'signature-pad-wrapper',
        'signature-button',
        'signature-canvas',
        'clear-signature-btn',
        'signature-success',
        'loading-spinner',
        'stagger-item',
        'form-input-animated',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
