<?php
/**
 * Add a txt export button into the toolbar
 */

namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class TXT extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.txt';
	protected static $json_linked = 'plugins.page.html.txt';

	private static $layout = 'txt';

	public static function add(&$buttons = array()) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$aeSession = \MarkNotes\Session::getInstance();
		$file = $aeSession->get('filename');
		$file = str_replace(DS, '/', $aeFiles->replaceExtension($file, self::$layout));

		$url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

		$aeSession = \MarkNotes\Session::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$filename = $aeSettings->getFolderDocs(true).$aeSession->get('filename');
		$filename  = $aeFiles->replaceExtension($filename, static::$layout);
		$filename = str_replace('/', DS, $filename);

		// Check if the file, once converted (note.docx) exists
		if (!$aeFiles->fileExists($filename)) {
			// The file didn't exists so, if this plugin is called, we need to
			// be able to run the Pandoc conversion utility, check that the utility is
			// correctly configured

			$aeConvert = \MarkNotes\Tasks\Convert::getInstance($aeSession->get('filename'), static::$layout, 'pandoc');
			if ($aeConvert->isValid()) {
				// Yes, Pandoc is correctly configured, we'll be
				// able to offer the conversion
				$bPandoc = true;
			} else { // if ($aeConvert->isValid())
				// Pandoc isn't installed correctly
				$bPandoc = false;
			}
		} else { // if (!$aeFiles->fileExists($filename))
			$bPandoc = true;
		} // if (!$aeFiles->fileExists($filename))

		// Call the fnPluginHTMLDOCX() function when Pandoc isn't installed
		$task = ($bPandoc ? 'file' : 'fnPluginHTMLTXT');

		// Get the button HTML code
		$buttons['export'][] = self::button(
			array(
				'title' => 'export_txt',
				'default' => 'Export the note as a TXT document',
				'id' => 'icon_txt',
				'icon' => 'file-text-o',
				'task' => $task
			)
		);
		/*
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$title = $aeSettings->getText('export_'.self::$layout, 'Export the note as a TXT document', true);

		$aeSession = \MarkNotes\Session::getInstance();
		$file = $aeSession->get('filename');
		$file = str_replace(DS, '/', $aeFiles->replaceExtension($file, self::$layout));

		// Get the default extension, as specified in the settings.json file
		//$default = $aeSettings->getTask()['default'] ?? 'reveal';
		//if ($default === self::$layout) {
			// The default extension is txt ==> no need to mention the extension
		//	$file = $aeFiles->removeExtension($file);
		//}

		$url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

		$buttons .=
			'<a id="icon_'.self::$layout.'" data-task="file" data-file="'.$url.$file.'" '.
				'href="#" title="'.$title.'">'.
				'<i class="fa fa-file-text-o" aria-hidden="true"></i>'.
			'</a>';
*/
		return true;
	}
/*
	protected static function canAdd() : bool
	{
		// Conversion requires that
		//    	1. the .txt file already exists OR
		//		2. the pandoc utility is present to allow the conversion

		if ($bReturn = parent::canAdd()) {
			// We can continue
			$bReturn = false;

			// Check if the .txt file already exists
			$aeSession = \MarkNotes\Session::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			if ($aeSession->get('filename', '')!=='') {
				$aeFiles = \MarkNotes\Files::getInstance();

				$filename = $aeSettings->getFolderDocs(true).$aeSession->get('filename');
				$filename  = $aeFiles->replaceExtension($filename, static::$layout);
				$filename = str_replace('/', DS, $filename);

				$bReturn = $aeFiles->fileExists($filename);
			}

			if (!$bReturn) {
				$aeConvert = \MarkNotes\Tasks\Convert::getInstance($aeSession->get('filename'), static::$layout, 'pandoc');

				if ($aeConvert->isValid()) {
					// Yes, correctly configured, we'll be able
					// to offer the conversion
					$bReturn = true;
				}
			} // if (!$bReturn)
		} // if ($bReturn = parent::canAdd())

		return $bReturn;
	}*/
}
