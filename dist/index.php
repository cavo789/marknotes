<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-03-20T20:12:50.339Z
*/?>
<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace AeSecure;

/**
 * Author : AVONTURE Christophe - https://www.aesecure.com
 *
 * Start written date : December 14 2016
 *
 * !!! PHP Version : This script has been developed for PHP 7+, the script won't run with PHP 5.x !!!
 *
 * Put this script in a website root folder (f.i. site /documentation) and create a subfolder called "docs"
 * (see settings.json, variable folder).

 * In "docs", create as many subfolders you want and store there your markdown (.md) files.

 * Access to this script with your browser like f.i. http://localhost/documentation/index.php

 * The script will display the list of all .md files (found in the folder's structure) and, by clicking on a .md file,
 * will display his html output (created on-the-fly), the html version will be saved on the disk.
 *
 * History : see changelog.
 * Documentation : https://github.com/cavo789/markdown/blob/master/readme.md
 * Demo : https://markdown.cavo789.com
 */

// Application name
define('APP_NAME', 'Notes management');

   // No timeout please
   set_time_limit(0);

   require_once __DIR__.'/classes/functions.php';
   require_once __DIR__.'/classes/markdown.php';

   $task=\AeSecure\Functions::getParam('task', 'string', 'main', false);

   // Create an instance of the class and initialize the rootFolder variable (type string)
   $aeSMarkDown = new \AeSecure\Markdown();
   $aeSMarkDown->process($task);
   unset($aeSMarkDown);
