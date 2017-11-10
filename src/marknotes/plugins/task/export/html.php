<?php
/**
 * Export the note as a .html file
 */

namespace MarkNotes\Plugins\Task\Export;

defined('_MARKNOTES') or die('No direct access allowed');

class HTML extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.html';
	protected static $json_options = '';

	private static $extension = 'html';

	/**
	 * Make the conversion
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$final = $aeFiles->removeExtension($params['filename']).'.'.static::$extension;
		$final = $aeSettings->getFolderDocs(true).$final;

		// Generate the file ... only if not yet there
		if (!$aeFiles->fileExists($final)) {
			// Display the HTML rendering of a note
			$aeTask = \MarkNotes\Tasks\Display::getInstance();

			// Get the HTML content
			$content = $aeTask->run($params);

			if (!$aeFiles->createFile($final, $content)) {
				$final = '';

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("Error while trying to create [".$final."]", "error");
				}
				/*<!-- endbuild -->*/
			}
		}  // 	if(!$aeFiles->fileExists($final))

		$params['output'] = $final;
		return true;
	}
}
