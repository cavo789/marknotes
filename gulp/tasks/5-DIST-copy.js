// Copy files in the DIST folder

var gulp   = require('gulp');
var config = require('../config').DIST_copy;
var pkg    = require('../config').pkg();

gulp.task('copy', function() {

   if (pkg.gulp.tasks.dist.copy.doit===0) return;
   
   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ Copy files from the source folder to '+config.dest+'                                █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src)    
      .pipe(gulp.dest(config.dest));
	  
});