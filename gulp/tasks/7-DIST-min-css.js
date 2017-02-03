var gulp     = require('gulp');
var cleanCSS = require('gulp-clean-css');      // https://www.npmjs.com/package/gulp-clean-css
var config   = require('../config').DIST_css;
var pkg      = require('../config').pkg();

gulp.task('cssmin', function() {

   if (pkg.gulp.tasks.dist.minify.css.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ Minify CSS                                                               █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src)
      .pipe(cleanCSS())
      .pipe(gulp.dest(config.dest));
   
});