<?php

/**
 * What are the actions to fired when MarkNotes is running the "docx" task ?
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class DOCX
{
    private static $extension = 'docx';

    public static function run(&$params = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
        $filename = $params['filename'] ?? '';

		// Derive the name of the file and set it as an absolute filename
		if ($filename!=='') {
			$filename = $aeFiles->removeExtension($filename).'.'.self::$extension;
			$filename = $aeSettings->getFolderDocs(true).$filename;
		}

        if ($aeFiles->fileExists($filename)) {

			// If already there, check if that file is most recent than the .md file
			$filenameMD = $aeFiles->removeExtension($filename).'.md';

			/*<!-- build:debug -->*/
			if ($aeSettings->getDevMode()) {
				// During the development mode, always recreate the file
				unlink($filename);
			}
			/*<!-- endbuild -->*/

			if ($aeFiles->fileExists($filename)) {
				if (filemtime($filenameMD) > filemtime($filename)) {
					// The .md file is most recent, delete the exported document since it's an old one
					unlink($filename);
				}
			}

        } // if ($aeFiles->fileExists($filename))

		if (!$aeFiles->fileExists($filename)) {

			// Run the conversion

	        $aeEvents = \MarkNotes\Events::getInstance();
	        $aeEvents->loadPlugins('content', self::$extension);
	        $args = array(&$params);

			// true = stop on the first plugin which return "true" i.e. has done the job
	        $aeEvents->trigger('export.'.self::$extension, $args, true);

		} // if (!$aeFiles->fileExists($filename))

		// Download or show an error

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
