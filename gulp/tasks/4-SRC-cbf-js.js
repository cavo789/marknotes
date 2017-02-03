// JS code beautifier

var gulp   = require('gulp');
var shell  = require('gulp-shell');
var config = require('../config').SRC_jscbf;
var pkg    = require('../config').pkg();

gulp.task('jscbf', function () {

   if (pkg.gulp.tasks.source.beautifier.js.doit===0) return;
   
   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ JS Code Beautifier (DON\'T PAY ATTENTION HERE TO THE EXIT CODE)           █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');

   return gulp.src(config.src, {read: false})
      .pipe(shell([
	     'phpcbf --standard=PSR2 --extensions=' + config.extension + ' ' +
		 '--encoding=' + pkg.gulp.editor.encoding + ' --tab-width=' + pkg.gulp.editor.tab_size + ' ' + 
		 '--no-patch ' +  // Needed to avoid diff.exe is not recognized error (https://github.com/squizlabs/PHP_CodeSniffer/issues/458)
		 '--ignore=' + config.exclude + ' ' +
		 config.src
      ]));
	
});