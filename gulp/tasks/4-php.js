// Process PHP (minify, lint, ...)

var gulp      = require('gulp');
var gulpif    = require('gulp-if');            // https://github.com/robrich/gulp-if
var phplint   = require('gulp-phplint');
var header   = require('gulp-header');         // https://www.npmjs.com/package/gulp-header

var config   = require('../config').php;
var settings = require('../config').settings;
var banner   = require('../config').banner;
var getSettings = require('../config').Settings;

gulp.task('php', function () {
	
   if (config.doit===false) return;

   return gulp.src(config.src)
      .pipe(phplint('',config.options))
	//.pipe(phplint.reporter('fail'))
      .pipe(phplint.reporter(function(file){
         var report = file.phplintReport || {};
         if (report.error) {
            console.error(report.message+' on line '+report.line+' of '+report.filename);
         }
      }))
      .pipe(header(banner,{pkg:getSettings}))
      .pipe(gulp.dest(config.dest));

})