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
		$aeSession = \MarkNotes\Session::getInstance();

		$final = $aeFiles->removeExtension($params['filename']).'.'.static::$extension;
		$final = $aeSettings->getFolderDocs(true).$final;

		// Get the HTML content
		$aeTask = \MarkNotes\Tasks\Display::getInstance();
		$content = $aeTask->run($params);

		// Generate the .html file ... only if not yet there
		// AND ONLY IF THE NOTE DOESN'T CONTAINS ENCRYPTED DATA
		// (otherwise would be no more encrypted in the .html file)
		// Display the HTML rendering of a note
		if ($aeSession->get('NoteContainsEncryptedData',false)==false) {
			if (!$aeFiles->exists($final)) {
				// Accentuated char nightmare : try first without using
				// the decode function. If not OK, then use utf8_decode
				$bReturn = $aeFiles->create($final, $content);
				if (!$bReturn) {
					$bReturn = $aeFiles->create(utf8_decode($final), $content);
					if (!$bReturn) {
						$final = '';
						/*<!-- build:debug -->*/
						if ($aeSettings->getDebugMode()) {
							$aeDebug = \MarkNotes\Debug::getInstance();
							$aeDebug->log("Error while trying to create [".$final."]", "error");
						}
						/*<!-- endbuild -->*/
					}
				}
			}  // 	if(!$aeFiles->exists($final))
			// Store the filename so the export->after->display
			// plugin knows which file should be displayed
			$params['output'] = $final;

		} else { // if ($aeSession->get('NoteContainsEncryptedData'
			$params['content'] = $content;
		}

		return true;
	}
}
