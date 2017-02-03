// Process js : jslinting

var gulp     = require('gulp');
var jshint   = require('gulp-jshint');     // https://www.npmjs.com/package/gulp-jshint
var gulpif   = require('gulp-if');         // https://github.com/robrich/gulp-if

var config   = require('../config').jslint;
var settings = require('../config').settings;

gulp.task('jslint', function() {
	
   if (config.doit===false) return;

   return gulp.src(config.src)
	  .pipe(jshint(config.options))
	  .pipe(jshint.reporter('jshint-stylish'));

});