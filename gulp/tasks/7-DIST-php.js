// Process PHP (minify, lint, ...)

var gulp        = require('gulp');
var gulpif      = require('gulp-if');           // https://github.com/robrich/gulp-if
var header      = require('gulp-header');       // https://www.npmjs.com/package/gulp-header
var htmlreplace = require('gulp-html-replace'); // Replace <!-- build:A_KEY --> .... <!-- endbuild --> by an empty string

var config      = require('../config').DIST_php;
var settings    = require('../config').settings;
var banner      = require('../config').banner;
var getSettings = require('../config').Settings;

gulp.task('php', function () {
	
   if (config.doit===false) return;

   console.log('\n████████████████████████████████████████████████████████████████████████████');
   console.log('█ PHP - Remove debug code and add header                                   █');
   console.log('████████████████████████████████████████████████████████████████████████████\n');
   
   return gulp.src(config.src)
	  .pipe(htmlreplace({		  
         'debug':'',    
         },{
            keepUnassigned:true,
            keepBlockTags:false
         })
      )
      .pipe(header(banner,{pkg:getSettings}))
      .pipe(gulp.dest(config.dest));

})