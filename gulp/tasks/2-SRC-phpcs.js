// Process PHP Code sniffer (https://github.com/squizlabs/PHP_CodeSniffer, doc : https://github.com/squizlabs/PHP_CodeSniffer/wiki)
// Verify quality of the code

var gulp   = require('gulp');
var shell  = require('gulp-shell');
var config = require('../config').SRC_phpcs;
var pkg    = require('../config').pkg();

gulp.task('phpcs', function () {

   if (pkg.gulp.tasks.source.code_quality.sniffer.doit===0) return;
   
   console.log('\n████████████████████████████████████████████████████████████████████████████')
   console.log('█ PHP Code Sniffer - Scan and detect syntax error                          █');
   console.log('█ Output to ' + config.log + '    █');
   console.log('████████████████████████████████████████████████████████████████████████████\n')
   
   return gulp.src(config.src, {read: false})
      .pipe(shell([
	     'phpcs --standard=PSR2 --tab-width=' + pkg.gulp.editor.tab_size + ' ' +
	     '--encoding=' + pkg.gulp.editor.encoding + ' --report=xml --report-file=' + config.log + ' ' +
		 '--ignore=' + config.exclude + ' ' +
		 config.src
      ]))
	
})