<?php

/**
 * When exporting to pdf, txt, docx, ... check if the exported file already
 * exists and, if so, check his creation date against the date of the note.
 *
 * If the note has been modified after the exportation was done (the exported file
 * is thus obsolete), remove that file and run the exportation code again.
 */

namespace MarkNotes\Plugins\Task\Export\Before;

defined('_MARKNOTES') or die('No direct access allowed');

class KillOldOnes extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.before.kill_old_ones';
	protected static $json_options = '';

	public static function run(&$params = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();

		// Retrieve the filename and add the .html extension
		$filename = $params['filename'] ?? '';
		$filename = $aeFiles->removeExtension($filename).'.html';

		// Derive the name of the file and set it as an absolute filename
		if ($filename!=='') {
			$filename = $aeFiles->removeExtension($filename).'.'.$params['extension'];
			$filename = $aeSettings->getFolderDocs(true).$filename;
		}

		// Never kill .md files !!!
		if ($params['extension']!=='md') {
			if ($aeFiles->fileExists($filename)) {
				// If already there, check if that file is most recent than the .md file
				$filenameMD = $aeFiles->removeExtension($filename).'.md';

				try {
					if ($aeFiles->fileExists($filename)) {
						if (filemtime($filenameMD) > filemtime($filename)) {
							/*<!-- build:debug -->*/
							if ($aeSettings->getDebugMode()) {
								$aeDebug = \MarkNotes\Debug::getInstance();
								$aeDebug->log('The note (.md file) is more recent than '.
								  $filename.' so remove it and generate a new version', 'debug');
							}
							/*<!-- endbuild -->*/

							// The .md file is most recent, delete the exported document
							// since it's an old one
							try {
								unlink($filename);
							} catch (Exception $e) {
								/*<!-- build:debug -->*/
								if ($aeSettings->getDebugMode()) {
									$aeDebug = \MarkNotes\Debug::getInstance();
									$aeDebug->log($e->getMessage(), "error");
								}
								/*<!-- endbuild -->*/
							}
						}
					}
				} catch (Exception $e) {
					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log($e->getMessage(), "error");
					}
					/*<!-- endbuild -->*/
				}
			} else { // if ($aeFiles->fileExists($filename))
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log('File ['.$filename. '] not found, '.
					  'nothing to remove', 'debug');
				}
				/*<!-- endbuild -->*/
			}
		} // if ($params['extension']!=='md')

		return true;
	}
}
