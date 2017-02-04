// CHMOD - Set the DIST folder read-only to prevent file's modification (should be done in SRC, not in DIST)

var gulp   = require('gulp');
var shell  = require('gulp-shell');
var config = require('../config').DIST_chmod;
var pkg    = require('../config').pkg();

gulp.task('chmod', function () {
	
   if (pkg.gulp.tasks.dist.chmod.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ CHMOD - Set ' + config.src + ' read-only            █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src, {read: false})
      .pipe(shell('attrib +r '+config.src+'\*.* /s'));

})