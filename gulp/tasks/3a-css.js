// Process css (minify, lint, ...)

var gulp     = require('gulp');
var csslint  = require('gulp-csslint');        // https://www.npmjs.com/package/gulp-csslint
var gulpif   = require('gulp-if');             // https://github.com/robrich/gulp-if
var stylish  = require('jshint-stylish');      // https://www.npmjs.com/package/jshint-stylish
var cleanCSS = require('gulp-clean-css');      // https://www.npmjs.com/package/gulp-clean-css

var config   = require('../config').css;
var settings = require('../config').settings;

gulp.task('css', function() {

   if (config.doit===false) return;

   return gulp.src(config.src)
      .pipe(gulpif(config.lint, csslint(config.options)))
      //.pipe(gulpif(config.lint, csslint.reporter(stylish)))
      .pipe(gulpif(config.minify, cleanCSS()))
      .pipe(gulp.dest(config.dest));
   
});