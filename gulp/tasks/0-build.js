var gulp        = require('gulp');
var runSequence = require('run-sequence'); // (https://www.npmjs.com/package/run-sequence)

var config   = require('../config');

/**
 * Run tasks in sequence
 *
 *  DELETE (delete)               Kill the _dist folder
 *     |--- COPY (copy)           Copy files from _src to _dist
 *         |--- CSS (css)         Optimize / lint css / js / json and html
 *         |--- JS (js)
 *         |--- JSON (json)
 *            |--- PHP (php)      Lint and minify
 *
 */

gulp.task('build', function(callback) {

  runSequence(
     'delete',
	 'copy',
	 ['css', 'js', 'json'],    // These tasks can be simultanously fired
	 'php',
	 callback);
	 
});