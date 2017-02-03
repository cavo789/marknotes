// JS : Add a banner at the top of .js files

var gulp   = require('gulp');
var header = require('gulp-header');     
var config = require('../config').DIST_js;
var pkg    = require('../config').pkg();
var banner = pkg.gulp.tasks.dist.banner.header;

gulp.task('jsbanner', function() {
	
   if (pkg.gulp.tasks.dist.banner.js.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████');   
   console.log('█ JS - Add banner                                                              █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src)
	  .pipe(header(banner.join("\n"),{info:pkg}))
	  .pipe(gulp.dest(config.dest));

});