// JSON linting

var gulp     = require('gulp');
var jsonlint = require('gulp-jsonlint');
var config   = require('../config').SRC_jsonlint;
var pkg    = require('../config').pkg();

gulp.task('jsonlint', function() {

   if (pkg.gulp.tasks.source.lint.json.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████')
   console.log('█ JSON Lint                                                                █');
   console.log('████████████████████████████████████████████████████████████████████████████\n')
   
   return gulp.src(config.src)
      .pipe(jsonlint(config.options))
      .pipe(jsonlint.reporter());
   
});