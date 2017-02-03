// PHP Code beautifier

var gulp   = require('gulp');
var shell  = require('gulp-shell');
var config = require('../config').SRC_phpcbf;
var pkg    = require('../config').pkg();

gulp.task('phpcbf', function () {

   if (pkg.gulp.tasks.source.beautifier.php.doit===0) return;
   
   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ PHP Code Beautifier (DON\'T PAY ATTENTION HERE TO THE EXIT CODE)          █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src, {read: false})
      .pipe(shell([
	     'phpcbf --standard=PSR2 --extensions=' + config.extension + ' ' +
		 '--encoding=' + pkg.gulp.editor.encoding + ' --tab-width=' + pkg.gulp.editor.tab_size + ' ' +
		 '--warning-severity=0 '+
		 '--no-patch ' +  // Needed to avoid diff.exe is not recognized error (https://github.com/squizlabs/PHP_CodeSniffer/issues/458)
		 '--ignore=' + config.exclude + ' ' +
		 config.src
      ]));
	
})