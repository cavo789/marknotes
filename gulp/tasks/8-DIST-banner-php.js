// PHP : Add a banner at the top of .php files

var gulp   = require('gulp');
var header = require('gulp-header');       // https://www.npmjs.com/package/gulp-header
var config = require('../config').DIST_php;
var pkg    = require('../config').pkg();
var banner = pkg.gulp.tasks.dist.banner.header;

gulp.task('phpbanner', function () {
	
   if (pkg.gulp.tasks.dist.banner.php.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ PHP - Add header                                                         █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src)
      .pipe(header(banner.join("\n"),{info:pkg}))
      .pipe(gulp.dest(config.dest));

})