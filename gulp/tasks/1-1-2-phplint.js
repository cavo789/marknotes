var gulp        = require('gulp');
var gulpif      = require('gulp-if');           // https://github.com/robrich/gulp-if
var phplint     = require('gulp-phplint');

var config      = require('../config').phplint;
var settings    = require('../config').settings;

gulp.task('phplint', function () {
	
   if (config.doit===false) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████')
   console.log('█ PHP Lint - Check PHP files agains syntax error                           █');
   console.log('█ If nothing is displayed, checks are OK.                                  █');
   console.log('████████████████████████████████████████████████████████████████████████████\n')
   
   return gulp.src(config.src)
      .pipe(phplint('',config.options))
      .pipe(phplint.reporter('fail'))
      .pipe(phplint.reporter(function(file){
         var report = file.phplintReport || {};
         if (report.error) {
            console.error(report.message+' on line '+report.line+' of '+report.filename);
         }
      }));

})