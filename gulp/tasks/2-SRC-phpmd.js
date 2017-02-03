// Process PHP Mess Detector, read more on https://phpmd.org/

var gulp   = require('gulp');
var shell  = require('gulp-shell');
var config = require('../config').SRC_phpmd;
var pkg    = require('../config').pkg();

gulp.task('phpmd', function () {

   if (pkg.gulp.tasks.source.code_quality.mess_detector.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████')
   console.log('█ PHP Mess Detector - Scan and detect syntax error                         █');
   console.log('█ Output to ' + config.log + '   █');
   console.log('████████████████████████████████████████████████████████████████████████████\n')
   
   return gulp.src(config.src, {read: false})
      .pipe(shell([
	     'phpmd ' + 
		    config.src +   // Folder to scan
			' html'+       // Output : html (can be text or xml)
			' codesize,unusedcode,naming,design,cleancode,controversial' + // Check to fire (see https://phpmd.org/rules/index.html)
            ' --exclude ' + config.exclude +    // Exclude folders
			' --reportfile ' + config.log
      ]))
	
})