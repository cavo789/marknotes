<?php
/**
 * Export the note as a .epub file
 */

namespace MarkNotes\Plugins\Task\Export;

defined('_MARKNOTES') or die('No direct access allowed');

class EPUB extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.epub';
	protected static $json_options = '';

	private static $extension = 'epub';

	public static function run(&$params = null) : bool
	{
		$bReturn = true;

		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// $params['filename'] is f.i. note.docx, retrieve the .md
		// filename
		$mdFilename = $aeFiles->removeExtension($params['filename']).'.md';

		// ----------------------------------------
		// Call the generic class for file conversion
		$aeConvert = \MarkNotes\Tasks\Convert::getInstance($mdFilename, static::$extension, 'pandoc');


		// Get the filename, once exported (f.i. notes.txt)
		$final = $aeConvert->getFileName();

		// Generate the file ... only if not yet there
		if (!$aeFiles->exists($final)) {
			// Check if pandoc is installed; if not, check if the
			// exported file already exists
			if (!$aeConvert->isValid()) {
				$bReturn = false;
			} else {
				$arrPandoc = $aeSettings->getPlugins(JSON_OPTIONS_PANDOC);

				// Read the content of the .md file
				$filename = $aeSettings->getFolderDocs(true).$params['filename'];

				// Derive filenames
				$slug = $aeConvert->getSlugName();
				$debugFile = $aeConvert->getDebugFileName();

				// Get a copy of the .md note (in /temp folder), run any plugins
				$tempMD = $aeConvert->createTempNote();
				$sScript = $aeConvert->getScript($tempMD, $final);
				$aeConvert->Run($sScript, $final);
			} // if (!$aeConvert->isValid())
		}// if(!$aeFiles->exists($final))

		// In case of error, there is no output at all
		$params['output'] = ($bReturn ? $final : '');
		return $bReturn;
	}
}
