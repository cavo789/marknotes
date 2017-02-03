var gulp        = require('gulp');
var runSequence = require('run-sequence'); // (https://www.npmjs.com/package/run-sequence)
var config      = require('../config');

/* Linting */
gulp.task('lint', function(callback) {

  runSequence(
     'phplint',
	 'jslint',
	 'csslint',
	 'jsonlint',
	 callback);
	 
});

/* Make the DIST files */
gulp.task('dist', function(callback) {

  runSequence(
     ['js_removedebug','php_removedebug'],
	 ['cssmin', 'jsmin'],
	 ['jsbanner','phpbanner'],
	 callback);
	 
});