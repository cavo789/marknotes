'use strict';

/** 
  Read the package.json file of the project and return an object with the JSON representation  
*/
function getPackage() {
   var fs = require('fs');
   var json = JSON.parse(fs.readFileSync('package.json'));
   return json;
}

var path = require("path");

var pkg      = getPackage();
var paths    = pkg.gulp.paths;   // shortcut to the gulp.paths node of the package.json file

module.exports = {
   backup: {
      src: [paths.source + '/**/*'],
	  dest: paths.archives
   },  
   SRC_phpcs: {
      src: paths.source+'/',  
	  exclude: '*/'+paths.exclude+'/*', // Process every php files except the libs folder  
	  log: path.join(__dirname+'/../'+paths.logging+'/php-code-sniffer.xml') // Make an absolute filename
   },
   SRC_phpmd: {
      src: paths.source,    
	  exclude: '*\\'+paths.exclude+'\\*', // Process every php files of the target folder except the libs folder
	  log: path.join(__dirname+'/../'+paths.logging+'/php-mess_detector.xml') // Make an absolute filename
   },
   SRC_phplint: {
      src: [paths.source + '/**/*.php','!' + paths.source + '/'+paths.exclude+'{,/**}'],  // Exclude libs folder
      options: {debug:false, clear:true, skipPassedFiles:true}
   },
   SRC_jslint: {
      src: [paths.source + '/**/*.js','!' + paths.source + '/'+paths.exclude+'{,/**}'],  // Exclude libs folder
      options:{}		 
   },   
   SRC_csslint: {
      src: [paths.source + '/**/*.css','!' + paths.source + '/'+paths.exclude+'{,/**}'],  // Exclude libs folder
      options:{}		 
   },
   SRC_jsonlint: {
      src: [paths.source + '/**/*.json','!' + paths.source + '/'+paths.exclude+'{,/**}'],  // Exclude libs folder
      options:{}		 
   },   
   SRC_phpcbf: {
	  extension: 'php',
	  exclude: '*/'+paths.exclude+'/*', // Process every php files except the libs folder
      src: paths.source //abspath+paths.source
   },
   SRC_jscbf: {
	  extension: 'js',
	  exclude: '*/'+paths.exclude+'/*', // Process every js files except the libs folder
      src: paths.source //abspath+paths.source
   },  
   SRC_csscbf: {
      src : [paths.source + '/**/*.css','!' + paths.source + '/'+paths.exclude+'{,/**}'],  // Exclude libs folder,
      dest: paths.source
   },  
   SRC_jsoncbf: {
      src:  [paths.source + '/**/*.json','!' + paths.source + '/'+paths.exclude+'{,/**}'],  // Exclude libs folder,
	  dest: paths.source,
      options:{}		 
   },
   DIST_delete: {
      dest: [paths.target]
   }, 
   DIST_copy: {
      src: [paths.source + '/**/*',                     // Copy all files under the _SRC folder but 
         paths.source + '/**/.htaccess',                //    included .htaccess (needed because "/*" ignore files without name (only extension))
         '!' + paths.source + '/libs/php_error{,/**}',  // === Don't copy these folders
         '!' + paths.source + '/libs/**/*.md',          //     No need to take .md files from /libs and subfolders
         '!' + paths.source + '/logs{,/**}',
         '!' + paths.source + '/nbproject{,/**}',
         '!' + paths.source + '/vendor{,/**}',
         '!' + paths.source + '/bower.json',            // === Don't copy these files
         '!' + paths.source + '/custom.css',
         '!' + paths.source + '/custom.js',
         '!' + paths.source + '/gulpfile.js',
         '!' + paths.source + '/post-composer.bat',
         '!' + paths.source + '/settings.json'
      ],
      dest: paths.target
   },
   DIST_css: {
      src: [paths.target + '/**/*.css','!' + paths.target + '/libs{,/**}'],  // Ignore the libs folder
      dest: paths.target
   },
   DIST_js: {
      src: [paths.target + '/**/*.js','!' + paths.target + '/libs{,/**}'],  // Ignore the libs folder 
      dest: paths.target
   },
   DIST_php: {
      doit: true,
      src: [paths.target + '/**/*.php','!' + paths.target + '/libs{,/**}'],  // Ignore the libs folder 
      dest: paths.target
   },  
   pkg:getPackage
};