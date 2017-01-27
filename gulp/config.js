'use strict';

var source = 'src';
var target = 'dist';

/** 
  Read the package.json file of the project and return an object with the JSON representation  
*/
function getSettings($node) {
   var fs = require('fs');
   var json = JSON.parse(fs.readFileSync('package.json'));
   return json;
}

module.exports = {
   banner: [
      '<?php',   
      '/**',
      '* <%= pkg().name %> - <%= pkg().description %>',
      '* @version   : <%= pkg().version %>',
      '* @author    : <%= pkg().author %>',
      '* @copyright : <%= pkg().license %> (c) 2016 - <%= new Date().getFullYear() %>',
      '* @url       : <%= pkg().homepage %>',
      '* @package   : <%= new Date().toISOString() %>', 
      '*/',
      '?>', 
      ''].join('\n'),
   delete: {
      dest: [target]
   }, 
   copy: {
      src: [source + '/**/*',                     // Copy all files under the _SRC folder but 
         source + '/**/.htaccess',                //    included .htaccess (needed because "/*" ignore files without name (only extension))
         '!' + source + '/libs/php_error{,/**}',  // === Don't copy these folders
         '!' + source + '/libs/**/*.md',          //     No need to take .md files from /libs and subfolders
         '!' + source + '/logs{,/**}',
         '!' + source + '/nbproject{,/**}',
         '!' + source + '/vendor{,/**}',
         '!' + source + '/bower.json',            // === Don't copy these files
         '!' + source + '/custom.css',
         '!' + source + '/custom.js',
         '!' + source + '/gulpfile.js',
         '!' + source + '/post-composer.bat',
         '!' + source + '/settings.json'
      ],
      dest: target
   },
   css: {
      doit: true, 
      src: [target + '/**/*.css',
         '!' + target + '/libs{,/**}'             // === Ignore libs folde
      ],
      lint: false,
      minify:true,
      options: {},
      dest: target
   },
   json: {
      doit: true, 
      src: [target + '/**/*.json',
      '!' + target + '/libs{,/**}'],
      lint: false,
      dest: target
   },
   js: {
      doit: true, 
      src: [target + '/**/*.js',
      '!' + target + '/libs{,/**}'],
      lint: false,
      minify:true,
      dest: target
   },
   php: {
      doit: true,
      minify: true,
      src: [target + '/**/*.php'],	
      lint: true,
      options: {debug:false, clear:true, skipPassedFiles:true},
      dest: target
   },
   Settings:getSettings
};
