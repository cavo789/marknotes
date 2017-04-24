<?php

/**
 * What are the actions to fired when MarkNotes is running the "epub" task ?
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class EPUB
{
    private static $extension = 'epub';

    public static function run(&$params = null)
    {
        // Display the HTML rendering of a note
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->loadPlugins('content', self::$extension);
        $args = array(&$params);
        $aeEvents->trigger('export.'. self::$extension, $args);

        $aeFiles = \MarkNotes\Files::getInstance();
        $filename = $params['output'] ?? '';

        if ($aeFiles->fileExists($filename)) {
            $aeDownload = \MarkNotes\Tasks\Download::getInstance();
            $aeDownload->run($filename, self::$extension);
        } else {
            $aeFunctions = \MarkNotes\Functions::getInstance();
            $aeFunctions->fileNotFound($filename);
        }
        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('run.task', __CLASS__.'::run');
        return true;
    }
}
