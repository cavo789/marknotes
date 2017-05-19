<?php

/**
 * What are the actions to fired when MarkNotes is running the "pdf" task ?
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class PDF
{
    private static $extension = 'pdf';

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

            // Read and show the pdf in the browser (inline), so don't force the download
            // of the file
            $content = file_get_contents($filename);
            header('Content-Type: application/pdf');
            header('Content-Length: '.strlen($content));
            header('Content-disposition: inline; filename="' . basename($filename) . '"');

            echo $content;
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
