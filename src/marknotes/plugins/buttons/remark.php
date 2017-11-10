<?php
/**
 * Add a Remark export button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Remark extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.page.remark';
	protected static $json_linked = 'plugins.page.html.remark';

	private static $ext = 'remark';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['slideshow'][] = self::button(
			array(
				'title' => 'export_remark',
				'default' => 'slideshow',
				'id' => 'icon_remark',
				'icon' => 'desktop',
				'task' => 'fnPluginHTMLRemark'
			)
		);
		/*
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$title = 'remark | '.$aeSettings->getText('slideshow', 'Slideshow', true);

		$aeSession = \MarkNotes\Session::getInstance();
		$file = $aeSession->get('filename');
		$file = str_replace(DS, '/', $aeFiles->replaceExtension($file, self::$ext));

		// Get the default extension, as specified in the settings.json file
		//$default = $aeSettings->getTask()['default'] ?? 'reveal';
		//if ($default === self::$ext) {
			// The default extension is remark ==> no need to mention the extension
		//	$file = $aeFiles->removeExtension($file);
		//}

		$url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

		$buttons .=
			'<a id="icon_'.self::$ext.'" data-task="file" data-file="'.$url.$file.'" '.
				'title="'.$title.'" href="#">'.
				'<i class="fa fa-desktop" aria-hidden="true"></i>'.
			  '</a>';
			  */
		return true;
	}
}
