// JSON code beautifier

var gulp     = require('gulp');
var beautify = require('gulp-json-format');
var config   = require('../config').SRC_jsoncbf;
var pkg      = require('../config').pkg();

gulp.task('jsoncbf', function () {

   if (pkg.gulp.tasks.source.beautifier.json.doit===0) return;
   
   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ JSON Code Beautifier                                                     █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src)
      .pipe(beautify(pkg.gulp.editor.tab_size))
      .pipe(gulp.dest(config.dest));
})