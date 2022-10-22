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

mix.js("resources/src/js/app.js", "public/js")
    .js("resources/src/js/ckeditor-classic.js", "public/js")
    .js("resources/src/js/ckeditor-inline.js", "public/js")
    .js("resources/src/js/ckeditor-balloon.js", "public/js")
    .js("resources/src/js/ckeditor-balloon-block.js", "public/js")
    .js("resources/src/js/ckeditor-document.js", "public/js")
    .css("resources/dist/css/_app.css", "public/css/app.css")
    .options({
        processCssUrls: false,
    })
    .copyDirectory("resources/src/json", "public/json")
    .copyDirectory("resources/src/fonts", "public/fonts")
    .copyDirectory("resources/src/images", "public/images");
