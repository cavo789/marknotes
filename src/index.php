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

include_once 'marknotes/includes/constants.php';

// Load third parties
include_once 'libs/autoload.php';

include_once 'autoload.php';
use \MarkNotes\Autoload;

if (version_compare(phpversion(), '7.0.0', '<')) {
    $root = dirname($_SERVER['SCRIPT_NAME']);
    $content = str_replace('%ROOT%', $root, file_get_contents(__DIR__.'/error_php.html'));
    echo $content;
} else {
    \MarkNotes\Autoload::register();

    // Application root folder.
    $folder = rtrim(str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME'])), DS).DS;

    $aeSettings = \MarkNotes\Settings::getInstance($folder);

    include_once 'marknotes/includes/debug.php';

    $aeFunctions = \MarkNotes\Functions::getInstance();
    $aeJSON = \MarkNotes\JSON::getInstance();

    $aeFunctions = \MarkNotes\Functions::getInstance();
    $task = $aeFunctions->getParam('task', 'string', 'main', false);

    $aeSMarkDown = new \MarkNotes\Markdown();
    $aeSMarkDown->process($task);
    unset($aeSMarkDown);
}
