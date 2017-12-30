<?php
/**
 * Add a epub export button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class EPUB extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.epub';
	protected static $json_linked = 'plugins.page.html.epub';

	private static $layout = 'epub';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['export'][] = self::button(
			array(
				'name' => 'epub',
				'title' => 'export_epub',
				'default' => 'Export the note as a EPUB document',
				'id' => 'icon_epub',
				'icon' => 'file-text-o',
				'task' => 'fnPluginHTMLEPUB'
			)
		);
		/*
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$title = $aeSettings->getText('export_'.self::$layout, 'Export the note as a EPUB document', true);

		$aeSession = \MarkNotes\Session::getInstance();
		$file = $aeSession->get('filename');
		$file = str_replace(DS, '/', $aeFiles->replaceExtension($file, self::$layout));

		// Get the default extension, as specified in the settings.json file
		//$default = $aeSettings->getTask()['default'] ?? 'reveal';
		//if ($default === self::$layout) {
			// The default extension is epub ==> no need to mention the extension
		//	$file = $aeFiles->removeExtension($file);
		//}

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

		$buttons .=
			'<a id="icon_'.self::$layout.'"  data-task="file" data-file="'.$url.$file.'" '.
				'title="'.$title.'" href="#">'.
				'<i class="fa fa-file-text-o" aria-hidden="true"></i>'.
			'</a>';
		*/
		return true;
	}

	protected static function canAdd() : bool
	{
		// Conversion requires that
		//    	1. the .epub file already exists OR
		//		2. the pandoc utility is present to allow the conversion

		if ($bReturn = parent::canAdd()) {
			// We can continue
			$bReturn = false;

			// Check if the .epub file already exists
			$aeSession = \MarkNotes\Session::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			if ($aeSession->get('filename', '')!=='') {
				$aeFiles = \MarkNotes\Files::getInstance();

				$filename = $aeSettings->getFolderDocs(true).$aeSession->get('filename');
				$filename  = $aeFiles->replaceExtension($filename, static::$layout);
				$filename = str_replace('/', DS, $filename);

				$bReturn = $aeFiles->exists($filename);
			}

			if (!$bReturn) {
				$aeConvert = \MarkNotes\Tasks\Convert::getInstance($aeSession->get('filename'), static::$layout, 'pandoc');

				if ($aeConvert->isValid()) {
					// Yes, correctly configured, we'll be able
					// to offer the conversion
					$bReturn = true;
				} else {
					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log("The epub plugin requires that Pandoc is installed and configured", "warning");
					}
					/*<!-- endbuild -->*/
				}
			} // if (!$bReturn)
		} // if ($bReturn = parent::canAdd())

		return $bReturn;
	}
}
