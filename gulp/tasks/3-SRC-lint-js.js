// Process js : jslinting

var gulp   = require('gulp');
var jshint = require('gulp-jshint');     // https://www.npmjs.com/package/gulp-jshint
var gulpif = require('gulp-if');         // https://github.com/robrich/gulp-if
var config = require('../config').SRC_jslint;
var pkg    = require('../config').pkg();

gulp.task('jslint', function() {
	
   if (pkg.gulp.tasks.source.lint.js.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████')
   console.log('█ JS Lint                                                                  █');
   console.log('████████████████████████████████████████████████████████████████████████████\n')
   
   return gulp.src(config.src)
	  .pipe(jshint(config.options))
	  .pipe(jshint.reporter('jshint-stylish'));

});