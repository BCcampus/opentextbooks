let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */
const assets = 'assets';
const dist = 'dist';
const node = 'node_modules'
const resources = 'inc';

mix.options({
    purifyCss: true,
    clearConsole: false
});

// scripts
mix.js(`${node}/jquery/dist/jquery.js`, `${dist}/scripts`)
    .js(`${node}/bootstrap/dist/js/bootstrap.js`, `${dist}/scripts`)
    .js(`${node}/popper.js/dist/popper.js`, `${dist}/scripts`)
    .js(`${node}/tablesorter/dist/js/jquery.tablesorter.min.js`, `${dist}/scripts`)
    .js(`${assets}/js/app.js`, `${dist}/scripts`)
    .extract(['jquery', 'bootstrap', 'popper.js']);

// styles
mix.copy(`${node}/bootstrap/dist/css/bootstrap.min.css`, `${dist}/styles`)
    .copy(`${node}/font-awesome/css/font-awesome.min.css`, `${dist}/styles`)
    // fonts
    .copy(`${node}/font-awesome/fonts/`, `${dist}/fonts`)

// BrowserSync
mix.browserSync({
    host: 'localhost',
    proxy: 'http://localhost/opentextbooks',
    port: 3000,
    files: [
        `${resources}/**/*.php`,
        `${dist}/**/*.css`,
        `${dist}/**/*.js`,
    ],
});

// Sass
mix.sass(`${assets}/css/custom.scss`, `${dist}/styles/custom.css`)

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.ts(src, output); <-- Requires tsconfig.json to exist in the same folder as webpack.mix.js
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.standaloneSass('src', output); <-- Faster, but isolated from Webpack.
// mix.fastSass('src', output); <-- Alias for mix.standaloneSass().
// mix.less(src, output);
// mix.stylus(src, output);
// mix.postCss(src, output, [require('postcss-some-plugin')()]);
// mix.browserSync('my-site.dev');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.babelConfig({}); <-- Merge extra Babel configuration (plugins, etc.) with Mix's default.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   globalVueStyles: file, // Variables file to be imported in every component.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   uglify: {}, // Uglify-specific options. https://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
