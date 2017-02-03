var gulp        = require('gulp');
var uglify      = require('gulp-uglify');     // https://www.npmjs.com/package/gulp-uglify
var config      = require('../config').DIST_js;
var pkg         = require('../config').pkg();

gulp.task('jsmin', function() {
	
   if (pkg.gulp.tasks.dist.minify.js.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████');   
   console.log('█ JS - Minify                                                              █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src)
	  .pipe(uglify())
	  .pipe(gulp.dest(config.dest));

});