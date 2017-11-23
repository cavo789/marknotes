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

		// Check if the file, once converted (note.txt) exists
		if (!$aeFiles->fileExists($filename)) {
			// The file didn't exists so, if this plugin is called, we need
			// to be able to run the Pandoc conversion utility, check that
			// the utility is correctly configured

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

		// Call the fnPluginHTMLTXT() function when Pandoc isn't installed
		$task = ($bPandoc ? 'file' : 'fnPluginHTMLTXT');

		// Get the button HTML code
		$buttons['export'][] = self::button(
			array(
				'name' => 'txt',
				'title' => 'export_txt',
				'default' => 'Export the note as a TXT document',
				'id' => 'icon_txt',
				'icon' => 'file-text-o',
				'task' => $task
			)
		);

		return true;
	}
}
