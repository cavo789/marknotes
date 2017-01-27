// Task #1 - Kill the _dist folder

var gulp   = require('gulp');
var del    = require('del');   // https://github.com/gulpjs/gulp/blob/master/docs/recipes/delete-files-folder.md
	
var config = require('../config.js').delete;
var settings = require('../config.js').settings;
 
gulp.task('delete', function() {
	
   del(config.dest);
   
   return true;
   
});