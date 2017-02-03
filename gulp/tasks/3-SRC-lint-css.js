// CSS Linting

var gulp     = require('gulp');
var csslint  = require('gulp-csslint');        // https://www.npmjs.com/package/gulp-csslint
var config   = require('../config').SRC_csslint;
var pkg      = require('../config').pkg();

csslint.addFormatter('csslint-stylish');

gulp.task('csslint', function() {

   if (pkg.gulp.tasks.source.lint.css.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████')
   console.log('█ CSS Lint                                                                 █');
   console.log('████████████████████████████████████████████████████████████████████████████\n')
   
   return gulp.src(config.src)
      .pipe(csslint(config.options))
      .pipe(csslint.formatter('stylish'));
   
});