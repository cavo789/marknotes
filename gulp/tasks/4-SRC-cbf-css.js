// CSS code beautifier

var gulp     = require('gulp');
var beautify = require('gulp-cssbeautify');
var config   = require('../config').SRC_csscbf;
var pkg      = require('../config').pkg();

gulp.task('csscbf', function () {

   if (pkg.gulp.tasks.source.beautifier.css.doit===0) return;
   
   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ CSS Code Beautifier                                                      █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src)
      .pipe(beautify())
      .pipe(gulp.dest(config.dest));

})