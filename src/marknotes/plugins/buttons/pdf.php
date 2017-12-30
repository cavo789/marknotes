<?php
/**
 * Add a pdf export button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class PDF extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.pdf';
	protected static $json_linked = 'plugins.page.html.pdf';

	private static $layout = 'pdf';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['export'][] = self::button(
			array(
				'name' => 'pdf',
				'title' => 'export_pdf',
				'default' => 'Export the note as a PDF document',
				'id' => 'icon_pdf',
				'icon' => 'file-pdf-o',
				'task' => 'fnPluginHTMLPDF'
			)
		);
		/*
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$title = $aeSettings->getText('export_'.self::$layout, 'Export the note as a PDF document', true);

		$aeSession = \MarkNotes\Session::getInstance();
		$file = $aeSession->get('filename');
		$file = str_replace(DS, '/', $aeFiles->replaceExtension($file, self::$layout));

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

		$buttons .=
			'<a id="icon_'.self::$layout.'" data-task="file" data-file="'.$url.$file.'" '.
				'title="'.$title.'" href="#">'.
				'<i class="fa fa-file-pdf-o" aria-hidden="true"></i>'.
			  '</a>';
		*/
		return true;
	}

	protected static function canAdd() : bool
	{
		// Conversion requires that
		//    	1. the .pdf file already exists OR
		//		2. the pandoc utility is present to allow the conversion

		if ($bReturn = parent::canAdd()) {
			// We can continue
			$bReturn = false;

			// Check if the .pdf file already exists
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
				}
			} // if (!$bReturn)
		} // if ($bReturn = parent::canAdd())

		return $bReturn;
	}
}
