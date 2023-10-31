/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",

        'node_modules/preline/dist/*.js',
        './src/**/*.{html,js}'
    ],
    theme: {
        extend: {},
    },
    darkMode: 'class',
    plugins: [
        require('flowbite/plugin'),
        require('@tailwindcss/forms'),
        require('preline/plugin')
    ],
}
