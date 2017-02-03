// PHP - Remove debugging code

var gulp        = require('gulp');
var htmlreplace = require('gulp-html-replace'); // Replace <!-- build:A_KEY --> .... <!-- endbuild --> by an empty string
var config      = require('../config').DIST_php;
var pkg         = require('../config').pkg();

gulp.task('php_removedebug', function () {
	
   if (pkg.gulp.tasks.dist.removedebug.php.doit===0) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ PHP - Remove debug code                                                  █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src)
	  .pipe(htmlreplace({		  
         'debug':'',    
         },{
            keepUnassigned:true,
            keepBlockTags:false
         })
      )
      .pipe(gulp.dest(config.dest));

})