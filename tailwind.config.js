import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    night: '#181A2F',     // Deep midnight background
                    surface: '#242E49',   // Elevated surface cards
                    slate: '#37415C',     // Slate borders and muted
                    peach: '#FDA481',     // Warm coral / peach glow
                    crimson: '#B4182D',   // Vivid crimson red
                    wine: '#54162B',      // Rich dark wine / maroon
                }
            },
            boxShadow: {
                'm3-1': '0px 1px 3px 1px rgba(0, 0, 0, 0.15), 0px 1px 2px 0px rgba(0, 0, 0, 0.3)',
                'm3-2': '0px 2px 6px 2px rgba(0, 0, 0, 0.2), 0px 1px 2px 0px rgba(0, 0, 0, 0.35)',
                'm3-3': '0px 4px 16px 3px rgba(0, 0, 0, 0.3), 0px 2px 6px 0px rgba(0, 0, 0, 0.4)',
                'm3-glow': '0 0 25px -5px rgba(253, 164, 129, 0.3), 0 0 15px -3px rgba(180, 24, 45, 0.35)',
            }
        },
    },

    plugins: [require('@tailwindcss/forms')],
};