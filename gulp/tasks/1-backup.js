// Make an archive : zip all files from the config.src folder and save that file in config.dest.
// Zip filename : derived from the package.json file, use name and version and create f.i. project_v1.0-20170203.zip)

var gulp   = require('gulp');
var zip    = require('gulp-zip');
var config = require('../config').backup;
var pkg    = require('../config').pkg();

gulp.task('backup', function() { 	

   if (pkg.gulp.tasks.backup.doit===0) return;
   
   // Retrieve the system date : yyyymmddHHMMss (20170203142512)
   var date = new Date().toISOString().replace(/[^0-9]/g, '').substring(0,14);
		
   gulp.src(config.src)
   
      // Make the ZIP  
      .pipe(zip(pkg.name+'_v'+pkg.version+'_'+date+'.zip'))
	  
	  // Save it
      .pipe(gulp.dest(config.dest));		  
   
   return true;	  

});