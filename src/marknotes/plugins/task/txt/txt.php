<?php

/**
 * What are the actions to fired when MarkNotes is running the "txt" task ?
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class TXT
{
    public static function run(&$params = null)
    {
        // Display the HTML rendering of a note
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->loadPlugins('content', 'txt');
        $args = array(&$params);

		// true = stop on the first plugin which return "true" i.e. has done the job
        $aeEvents->trigger('export.txt', $args, true);

        header('Content-Type: text/plain; charset=utf-8');

        $aeFiles = \MarkNotes\Files::getInstance();
        $filename = $params['output'] ?? '';

		/*<!-- build:debug -->*/
		if ($aeSettings->getDevMode()) {
			// During the development mode, always recreate the file
			unlink($filename);
		}
		/*<!-- endbuild -->*/

//        if (!$aeFiles->fileExists($filename)) {
//            $filename = utf8_encode($filename);
//        }

        if ($aeFiles->fileExists($filename)) {
            $content = file_get_contents(mb_convert_encoding($filename, "ISO-8859-1", "UTF-8"));
            echo $content;
        } else {
            $aeFunctions = \MarkNotes\Functions::getInstance();
            $aeFunctions->fileNotFound($params['output']);
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
