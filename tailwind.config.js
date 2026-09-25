import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                // A cold, neutral frame; colour comes from the artwork.
                ink: '#0A0C0F',
                slab: '#11151A',
                slab2: '#181D23',
                rule: '#232A32',
                rule2: '#323B45',
                frost: '#E9EEF2',
                mist: '#8E9AA6',
                dim: '#626D78',
                signal: '#E5383B',
            },
            fontFamily: {
                sans: ['"Schibsted Grotesk"', '"Helvetica Neue"', 'Arial', ...defaultTheme.fontFamily.sans],
                display: ['"Big Shoulders Display"', '"Arial Narrow"', 'Impact', ...defaultTheme.fontFamily.sans],
                body: ['"Schibsted Grotesk"', '"Helvetica Neue"', 'Arial', ...defaultTheme.fontFamily.sans],
                meta: ['"Martian Mono"', ...defaultTheme.fontFamily.mono],
            },
            maxWidth: {
                site: '1320px',
            },
        },
    },
    plugins: [],
};
