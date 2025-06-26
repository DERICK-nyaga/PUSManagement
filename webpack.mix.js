const mix = require('laravel-mix');

// Process your CSS file
mix.postCss('/css/modifiedstyles.css', 'public/css', [
    require('postcss-import'),
    require('autoprefixer'),
]);
mix.postCss('/css/fixedstyles.css', 'public/css', [
    require('postcss-import'),
    require('autoprefixer'),
]);
// const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/modifiedstyles.css', 'public/css', [
       require('postcss-import'),
       require('autoprefixer'),
   ]);

   mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/fixedstyles.css', 'public/css', [
       require('postcss-import'),
       require('autoprefixer'),
   ]);

mix.js('resources/js/payments.js', 'public/js')
   .postCss('resources/css/modifiedstyles.css', 'public/css');
