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

			if (filemtime($filenameMD) > filemtime($filename)) {
				// The .md file is most recent, delete the exported document since it's an old one
				unlink($filename);
			}

		} // if ($aeFiles->fileExists($filename))

		if (!$aeFiles->fileExists($filename)) {

			// Run the conversion

			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->loadPlugins('content', self::$extension);
			$args = array(&$params);
			$aeEvents->trigger('export.'.self::$extension, $args);

		} // if (!$aeFiles->fileExists($filename))

		// Download or show an error

		if ($aeFiles->fileExists($filename)) {

			// Read and show the pdf in the browser (inline), so don't force the download
            // of the file
            $content = file_get_contents(mb_convert_encoding($filename, "ISO-8859-1", "UTF-8"));
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
