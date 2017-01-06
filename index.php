<?php                 

/* REQUIRES PHP 7.x AT LEAST */

/**
 * Author : AVONTURE Christophe - https://www.aesecure.com
 * 
 * Written date : December 14 2016 
 * 
 * PHP Version : This script has been developed for PHP 7+, the script won't run with PHP 5.x
 * 
 * Put this script in a website root folder (f.i. site /documentation) and create a subfolder called "docs" (see DOC_FOLDER constant).
 * In "docs", create as many subfolders you want and store there your markdown (.md) files.
 * Access to this script with your browser like f.i. http://localhost/documentation/index.php 
 * The script will display the list of all .md files (found in the folder's structure) and, by clicking on a .md file,
 * will display his html output (created on-the-fly), the html version will be saved on the disk. 
 * 
 * History :
 * 
 * 2017-01-06 : + Move assets from inline to /assets folder
 *              + Move classes into /classes folder
 *              + Move HTML inline to /templates folder
 *              + Add support for custom template (property template in settings.json)
 * 2017-01-05 : + Add custom.js to allow the end user to add his own script
 *              + Add a IMG_MAX_WIDTH constant to be sure that images will be correctly resized if too big
 *              + Add selectize.js
 * 2017-01-04 : + Add Print preview feature (thanks to https://github.com/etimbo/jquery-print-preview-plugin)
 *              + Remove icons (images) and use font-awesome instead
 * 2017-01-03 : + Improve add icons (based on jQuery and no more pure css)
 *              + Add filtering on folder name : just click on a folder name and the list will be limited to that folder
 *              + Start editing code
 *              + Remove leading / ending spaces before searching
 *              + Add Google font support (node "page::google_font" in the settings.json file)
 * 2016-12-30 : + Search supports encrypted data now
 * 2016-12-21 : + Add search functionality, add comments, add custom.css, 
 *              + Add change a few css to try to make things clearer, force links (<a href="">) to be opened in a new tab
 * 2016-12-19 : + Add support for encryption (tag <encrypt>)
 * 2016-12-14 : First version
 */

// PHP 7 : force the use of the correct type
declare(strict_types=1);

define('DEBUG',TRUE);

// Application name
define('APP_NAME','Notes management (c) Christophe Avonture');

// No timeout please
set_time_limit(0);

   require_once(__DIR__.'/classes/functions.php');
   require_once(__DIR__.'/classes/markdown.php');
   
   if (DEBUG===TRUE) {
      ini_set("display_errors", "1");
      ini_set("display_startup_errors", "1");
      ini_set("html_errors", "1");
      ini_set("docref_root", "http://www.php.net/");
      ini_set("error_prepend_string", "<div style='color:black;font-family:verdana;border:1px solid red; padding:5px;'>");
      ini_set("error_append_string", "</div>");
      error_reporting(E_ALL);
   } else {	   
      ini_set('error_reporting', E_ALL & ~ E_NOTICE);	  
   }
   
   $task=aeSecureFct::getParam('task','string','main',false);

   // Create an instance of the class and initialize the rootFolder variable (type string)
   $aeSMarkDown = new aeSecureMarkdown((string) dirname($_SERVER['SCRIPT_FILENAME']).'/');
   $aeSMarkDown->process($task);  
   unset($aeSMarkDown);

?>