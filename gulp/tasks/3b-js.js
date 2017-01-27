// Process js (minify, lint, ...)

var gulp     = require('gulp');
var jshint   = require('gulp-jshint');     // https://www.npmjs.com/package/gulp-jshint
var header   = require('gulp-header');     // https://www.npmjs.com/package/gulp-header
var uglify   = require('gulp-uglify');     // https://www.npmjs.com/package/gulp-uglify
var gulpif   = require('gulp-if');         // https://github.com/robrich/gulp-if
var stylish  = require('jshint-stylish');  // https://www.npmjs.com/package/jshint-stylish

var config   = require('../config').js;
var settings = require('../config').settings;
var banner   = require('../config').banner;

var getSettings = require('../config').Settings;

gulp.task('js', function() {
	
   if (config.doit===false) return;

   return gulp.src(config.src)
	  .pipe(gulpif(config.lint, jshint(config.options)))
	  .pipe(gulpif(config.lint, jshint.reporter(stylish)))
	  .pipe(gulpif(config.minify, uglify()))
	  .pipe(header(banner,{pkg:getSettings}))
	  .pipe(gulp.dest(config.dest));

});