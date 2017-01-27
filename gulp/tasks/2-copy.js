// Task #2 - Copy files from the _src folder and copy them in _dist

var gulp        = require('gulp');
var htmlreplace = require('gulp-html-replace'); // Replace <!-- build:A_KEY --> .... <!-- endbuild --> by an empty string

var config   = require('../config').copy;
var settings = require('../config').settings;

gulp.task('copy', function() {

   return gulp.src(config.src)
     // .pipe(htmlreplace({		  
     //    'debug':'',    
     //    },{
     //       keepUnassigned:true,
     //       keepBlockTags:true                        // Important : si false, les autres blocs sont supprimés (bug???)
     //    })
     //  )
      .pipe(gulp.dest(config.dest));
	  
});