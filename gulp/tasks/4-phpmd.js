// Process PHP Mess Detector, read more on https://phpmd.org/

var gulp      = require('gulp');
var shell     = require('gulp-shell');

var config    = require('../config').phpmd;
var settings  = require('../config').settings;

gulp.task('phpmd', function () {

   if (config.doit===false) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████')
   console.log('█ PHP Mess Detector - Results have been outputted in the /log folder       █');
   console.log('█                   exit code 2 : errors have been found                   █');
   console.log('████████████████████████████████████████████████████████████████████████████\n')
   
   return gulp.src(config.src, {read: false})
      .pipe(shell([
	     //'mkdir ' + config.log,
	     'phpmd ' + 
		    config.src +   // Folder to scan
			' html'+       // Output : html (can be text or xml)
			' codesize,unusedcode,naming,design,cleancode,controversial' + // Check to fire (see https://phpmd.org/rules/index.html)
            ' --exclude ' + config.exclude +    // Exclude folders
			' --reportfile ' + config.log + '/phpmd.html' // Output the log into the /logs/phpmd.html file
      ]))
	
})