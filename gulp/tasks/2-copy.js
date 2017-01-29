// Task #2 - Copy files from the _src folder and copy them in _dist

var gulp        = require('gulp');

var config   = require('../config').copy;
var settings = require('../config').settings;

gulp.task('copy', function() {

   return gulp.src(config.src)    
      .pipe(gulp.dest(config.dest));
	  
});