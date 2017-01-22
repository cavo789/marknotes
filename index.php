<?php                 
declare(strict_types=1);

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
 * 2017-01-29 : + Code reorganization
 *              + The edit form show unencrypted infos to let the user to update them
 *              + add jsPDF for Javascript pdf exportation
 * 2017-01-28 : + Add Encrypt button in the editor
 * 2017-01-20 : + Edit mode 
 *              + Sanitize filename
 *              + Add .htaccess security, no script execution in /docs
 * 2017-01-19 : + Add settings->list->opened
 *              + First initialize the program by reading settings.json.dist file
 *              + Replace highlight.js by Prism (for language syntax highlighting)
 *              - Remove tagging in the .html file, do it only "on-the-fly"
 * 2017-01-18 : + Keep jsTree as compact as possible : after a search, close all nodes and show only ones with selected node
 * 2017-01-17 : + Add "table" class to tables
 *              + Add jsTree
 *              + Add DataTables plugin
 *              - Don't rewrite the .md file anymore for adding tags; do it only on-the-fly
 * 2017-01-16 : + Add aeSecureJSON class for better JSON handling (error handling)
 *              + If translated string isn't in settings.json, use the one of settings.json.dist
 *              + Add images lazyload (see settings.json -> optimisation -> lazyload
 * 2017-01-15 : + Add Debug and Development entries in settings.json
 *              + Replace editor by a boolean in settings.json
 *              + Auto tagging regex improved
 * 2017-01-14 : + Add automatically known tags in markdown existing files
 * 2017-01-13 : + Javascript improvements
 *              + CSS improvements
 *              + Add a Slideshow button to display the note like a slideshow
 *              + libs folder reorganization
 *              + add linkify.js to convert plain text email, urls, ... into clickable ones
 *              + search : add a auto keyword i.e. a filtering that is immediatly done when showing the application screen
 *              - remove highlite and replace by jquery.highlight.js
 * 2017-01-12 : + Tags : maintain automatically the tags.json file. Just need to put §Some_Tag in a document (une § and not #)
 *              + Tags : detect tags in JS and allow to click on it for filtering
 *              + Wallpaper image : only visible for large screen
 * 2017-01-11 : + Search : allow to specify more than one term (boolean operator is AND)
 *              + Add highlight.js for syntax color
 * 2017-01-10 : - Remove selective.js
 *              + Add Flexdatalist jquery
 * 2017-01-09 : + Add support of links toward another notes 
 *              + Add the copy the link in the clipboard feature
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

// Application name
define('APP_NAME','Notes management (c) Christophe Avonture');

   // No timeout please
   set_time_limit(0);

   require_once(__DIR__.'/classes/functions.php');
   require_once(__DIR__.'/classes/markdown.php');
   
   $task=aeSecureFct::getParam('task','string','main',false);

   // Create an instance of the class and initialize the rootFolder variable (type string)
   $aeSMarkDown = new aeSecureMarkdown();
   $aeSMarkDown->process($task);  
   unset($aeSMarkDown);

?>