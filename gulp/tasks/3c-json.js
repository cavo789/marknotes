// Process json (minify, lint, ...)

var gulp     = require('gulp');
var jsonlint = require('gulp-jsonlint');   // https://www.npmjs.com/package/gulp-jsonlint
var gulpif   = require('gulp-if');         // https://github.com/robrich/gulp-if
var stylish  = require('jshint-stylish');  // https://www.npmjs.com/package/jshint-stylish)

var config   = require('../config').json;
var settings = require('../config').settings;

gulp.task('json', function() {

   if (config.doit===false) return;

   return gulp.src(config.src) 
	  .pipe(gulpif(config.lint, jsonlint()))
	  .pipe(gulpif(config.lint, jsonlint.reporter(stylish)))
	  .pipe(gulp.dest(config.dest));

});