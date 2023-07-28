// https://medium.com/@defrian.yarfi/https-medium-com-defrian-yarfi-upgrade-gulp-3-to-gulp-4-the-gulpfile-js-workflow-45196e333b86

// Defining requirements
const { gulp, src, dest, parallel, series, watch } = require('gulp');
const rename = require('gulp-rename');
const sass = require('gulp-sass')(require('sass'));
const prefix = require('gulp-autoprefixer');
const sourcemaps = require('gulp-sourcemaps');
const concat = require('gulp-concat');
const plumber = require('gulp-plumber');
const browserSync = require( 'browser-sync' ).create();

// Optimizations
const flatten = require('gulp-flatten');
const terser = require('gulp-terser'); // js optimizer
const stripJs = require('gulp-strip-comments'); // remove js comments
const stripCss = require('gulp-strip-css-comments'); // remove css comments
const cleanCSS = require('gulp-clean-css'); // minify css
const purgecss = require('gulp-purgecss'); // remove unused classes
const purgecssWordpress = require('purgecss-with-wordpress');

// js bundle with rollup
const path = require('path');
const source = require('vinyl-source-stream');
const buffer = require('vinyl-buffer');
const rollup = require('@rollup/stream');

// *Optional* Depends on what JS features you want vs what browsers you need to support
// *Not needed* for basic ES6 module import syntax support
// var { babel } = require('@rollup/plugin-babel');

// Alias
const alias = require('@rollup/plugin-alias');

// Add support for importing from node_modules folder like import x from 'module-name'
const { nodeResolve } = require('@rollup/plugin-node-resolve');

// Cache needs to be initialized outside of the Gulp task
// var cache;

// Configuration file to keep your code DRY
const cfg = require( './gulpconfig.json' );
const paths = cfg.paths;

// Utils
const beep = require('beepbeep');



function css() {
    return src( paths.assets + '/styles/*.scss' )
        .pipe( plumber({
          handleError: function (err) {
            console.log( err );
            this.emit( 'end' );
          }
        }))
        .pipe(sourcemaps.init())
        .pipe( sass( {
          errLogToConsole: true,
          includePaths: ['./node_modules']} // pesca @import direttamente da node
         ) )
        .pipe(prefix(['last 2 versions'], {
            cascade: true
        }))
        .pipe(stripCss())
        .pipe( cleanCSS( { compatibility: '*' } ) )
        .pipe( rename( { suffix: '.min' } ) )
        .pipe(sourcemaps.write('.'))
        .pipe( dest( paths.dist + '/styles' ) );
}


// Purge css
function purgeWpCss() {
    return src( paths.dist + '/styles/theme.min.css' )
        .pipe(purgecss({
            content: [
              '**/*.php',
              '**/*.html'
              // '**/*.js'
            ],
            // safelist: [
            //   ...purgecssWordpress.safelist,
            //   paths.assets + '/styles/components/*.scss',
            //   paths.assets + '/styles/layouts/*.scss',
            //   paths.assets + '/styles/vendors/*.scss'
            // ]
        }))
        .pipe( rename( { suffix: '.bundle' } ) )
        .pipe(dest(paths.dist + '/styles'));
}


// JS bundled into min.js task
// https://stackoverflow.com/questions/47632435/es6-import-module-with-gulp
function js() {
  return rollup({
      // Point to the entry file
      input: paths.assets + '/scripts/app.js',
      context: 'this',

      // Apply plugins
      plugins: [
        // babel(),
        nodeResolve(),
        // https://www.npmjs.com/package/@rollup/plugin-alias
        alias({
          entries: [
            {
              find: '@scripts',
              replacement: path.resolve(__dirname, './assets/scripts/')
              // OR place `customResolver` here. See explanation below.
            }
          ]
        })
      ],

      // https://github.com/lukeed/navaid/issues/5
      inlineDynamicImports: true,

      // Use cache for better performance
      // cache: cache,

      // Note: these options are placed at the root level in older versions of Rollup
      output: {
        // Output bundle is intended for use in browsers
        // (iife = "Immediately Invoked Function Expression")
        format: 'iife',
        sourcemap: true,
      }
    })

    // .on('bundle', function(bundle) {
    //   // Update cache data after every bundle is created
    //   cache = bundle;
    // })

    // Name of the output file.
    .pipe(source( 'theme.min.js' ))
    .pipe(buffer())
    .pipe(stripJs())
    .pipe(terser({
      sourceMap: {
        filename: paths.dist + '/scripts/theme.min.js',
        url: paths.dist + '/scripts/theme.min.js.map'
      }
    }))

    // The use of sourcemaps here might not be necessary,
    // Gulp 4 has some native sourcemap support built in
    .pipe(sourcemaps.write('.'))

    // Where to send the output file
    .pipe(dest( paths.dist + '/scripts' ));
}



/**
 * Copy assets directory
 */
function copyFonts() {
    // Copy assets
    return src([paths.assets + '/fonts/**'],
        // del(paths.dist + '/fonts/**/*')
    )
    // .pipe( flatten() )
    .pipe(dest(paths.dist + '/fonts'));
}


/**
 * Copy assets directory
 */
function copyImages() {
    // Copy assets
    return src([paths.assets + '/images/**'],
        // del(paths.dist + '/images/**/*')
    )
    // .pipe( imagemin() )
    .pipe(dest(paths.dist + '/images'));
}


// BrowserSync
function rwBrowserSync() {
    browserSync.init( cfg.browserSyncWatchFiles, cfg.browserSyncOptions );
}

// BrowserSync reload
function rwBrowserReload () {
    return browserSync.reload;
}


// Watch files
function watchFiles() {
    // Watch SCSS changes
    // watch(paths.assets + '/styles/**/*.scss', series(css, purgeWpCss))
    watch(paths.assets + '/styles/**/*.scss', series(css))
    .on('change', rwBrowserReload());
    // Watch javascripts changes
    watch(paths.assets + '/scripts/**/*.js', parallel(js))
    .on('change', rwBrowserReload());
}

// BrowserSync reload
function beeper () {
    beep();
    return true;
}

const watching = series( parallel(watchFiles, rwBrowserSync), beeper);

exports.js = js;
exports.css = css;
exports.purge = purgeWpCss;
exports.build = parallel(copyFonts, copyImages, js, series(css, purgeWpCss));
exports.default = watching;
