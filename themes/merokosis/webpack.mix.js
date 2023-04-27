
let mix = require('laravel-mix');

mix.options({ processCssUrls: false })
   .js('assets/js/src/theme.js', 'assets/js/')
   .minify('assets/js/theme.js')
   .minify('assets/css/theme.css')
   .sass('assets/scss/theme.scss', 'assets/css', {
    sassOptions: {
        outputStyle: 'nested',
    },
    implementation: require('node-sass') // Switch from Dart to node-sass implementation
});
