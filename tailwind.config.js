/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ['./resources/**/*.blade.php', './resources/**/*.vue', './resources/**/*.ts'],
    theme: {
        extend: {
            fontFamily: {
                gym: ['"Bebas Neue"', 'sans-serif'],
            },
        },
    },
    darkMode: 'class',
};

