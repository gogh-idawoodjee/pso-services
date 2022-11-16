const mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js()
    //    .js("resources/src/js/ckeditor-classic.js", "public/js") "resources/src/js/app.js", "public/js"
    // .css("resources/dist/css/_app.css", "public/css/app.css")
    .options({
        processCssUrls: false,
    })
