<?php
/* REQUIRES PHP 7.x AT LEAST */
/**
 * Author : AVONTURE Christophe - https://www.aesecure.com
 *
 * Documentation : https://github.com/cavo789/marknotes/wiki
 * Demo : https://marknotes.cavo789.com
 * History : https://github.com/cavo789/marknotes/blob/master/changelog.md
 */

namespace MarkNotes;

define('_MARKNOTES', 1);

// Load third parties
include_once 'libs/autoload.php';

include_once 'autoload.php';
use \MarkNotes\Autoload;

if (version_compare(phpversion(), '7.0.0', '<')) {
    $root = dirname($_SERVER['SCRIPT_NAME']);
    $content = str_replace('%ROOT%', $root, file_get_contents(__DIR__.'/error_php.html'));
    echo $content;
} else {
    include_once 'marknotes/constants.php';

    \MarkNotes\Autoload::register();

    /*<!-- build:debug -->*/
    $aeDebug = \MarkNotes\Debug::getInstance();
    /*<!-- endbuild -->*/

    // Application root folder.
    $folder = str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME']));
    $folder = rtrim($folder, DS).DS;

    $aeSettings = \MarkNotes\Settings::getInstance($folder);
    $aeFunctions = \MarkNotes\Functions::getInstance();
    $aeJSON = \MarkNotes\JSON::getInstance();

    /*<!-- build:debug -->*/
    $aeJSON->debug($aeSettings->getDebugMode());
    /*<!-- endbuild -->*/

    // No timeout please
    set_time_limit(0);

    $aeFunctions = \MarkNotes\Functions::getInstance();
    $task = $aeFunctions->getParam('task', 'string', 'main', false);

    // Create an instance of the class and initialize the rootFolder variable (type string)
    $aeSMarkDown = new \MarkNotes\Markdown();
    $aeSMarkDown->process($task);
    unset($aeSMarkDown);
}
