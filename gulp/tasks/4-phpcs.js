// Process PHP Code sniffer (https://github.com/squizlabs/PHP_CodeSniffer, doc : https://github.com/squizlabs/PHP_CodeSniffer/wiki)

var gulp      = require('gulp');
var shell     = require('gulp-shell');

var config    = require('../config').phpcs;
var settings  = require('../config').settings;

gulp.task('phpcs', function () {

   if (config.doit===false) return;

   
   console.log('\n████████████████████████████████████████████████████████████████████████████')
   console.log('█ PHP Code Sniffer - Scan and detect syntax error                          █');
   console.log('█                   exit code 2 : errors have been found                   █');
   console.log('████████████████████████████████████████████████████████████████████████████\n')
   
   return gulp.src(config.src, {read: false})
      .pipe(shell([
	     'phpcs --standard=PSR2 --tab-width=4' + 
	     '--encoding=utf-8 --report-file=' + config.log + ' ' +
		 config.src
      ]))
	
})