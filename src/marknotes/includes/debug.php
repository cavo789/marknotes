<?php

/**
 * Initialization of the Debug class; file included in the index.php and router.php file, during
 * the loading time of the application so the debugging functionnality is ready as soon as possible.
 */

defined('_MARKNOTES') or die('No direct access allowed');

/*<!-- build:debug -->*/
$aeDebug = \MarkNotes\Debug::getInstance();

// Application root folder.
$folder = rtrim(str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME'])), DS).DS;

if (is_file($fname = $folder.'settings.json')) {
    $aeJSON = \MarkNotes\JSON::getInstance();
    $json = $aeJSON->json_decode($fname, true);
    $devMode = $json['development'] ?? 0;
    $timeZone = $json['timezone'] ?? 'Europe/London';
    if (isset($json['debug'])) {
        $aeDebug->enable($devMode, $timeZone);
    }
}
/*<!-- endbuild -->*/
