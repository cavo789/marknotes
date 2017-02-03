// Kill the DIST folder

var gulp   = require('gulp');
var del    = require('del'); 
var config = require('../config.js').DIST_delete;
var pkg    = require('../config').pkg();
 
gulp.task('delete', function() {
	
   if (pkg.gulp.tasks.dist.delete.doit===0) return;
   
   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ Kill folder '+config.dest+'                                                         █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   del(config.dest);
   
   return true;
   
});