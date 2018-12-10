const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application as well as publishing vendor resources.
 |
 */

elixir((mix) => {
    // mix.sass('app.scss')
       // .webpack('app.js');
	mix.scripts([
        'notific8.js',
        'jquery.notific8.js',
        'museumApp.js',
        'bower_components/seiyria-bootstrap-slider/src/js/bootstrap-slider.js'
    ]);

    mix.sass([
    	//todo!
        // 'museumdates.scss',
        '../js/bower_components/notific8/src/sass/notific8.scss',
        '../js/bower_components/seiyria-bootstrap-slider/src/sass/bootstrap-slider.scss'
	], 'public/css/all.css')
});
