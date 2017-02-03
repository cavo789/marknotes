var gulp     = require('gulp');
var zip      = require('gulp-zip');               // Création d'un zip (https://www.npmjs.com/package/gulp-zip)

var config   = require('../config').zip;
var settings = require('../config').settings;

gulp.task('zip', function() { 	

   var date = new Date().toISOString().replace(/[^0-9]/g, '').substring(0,14);
		
   gulp.src(config.src)
      .pipe(zip(config.name+'_'+date+'.zip'))
      .pipe(gulp.dest(config.dest));		  
   
   return true;	  

});