var gulp     = require('gulp');
var uglify   = require('gulp-uglify');     // https://www.npmjs.com/package/gulp-uglify
var gulpif   = require('gulp-if');         // https://github.com/robrich/gulp-if

var config   = require('../config').js;
var settings = require('../config').settings;

var getSettings = require('../config').Settings;

gulp.task('js', function() {
	
   if (config.doit===false) return;

   return gulp.src(config.src)
	  .pipe(gulpif(config.minify, uglify()))
	  .pipe(gulp.dest(config.dest));

});