<?php
/**
 * Add CSS and JS for the file manager
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class FileManager extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.filemanager';
	protected static $json_options = 'plugins.options.page.html.filemanager';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$rootURL = rtrim($aeFunctions->getCurrentURL(), '/');
		$url = $rootURL.'/marknotes/plugins/page/html/filemanager/';

		$script = "<script src=\"".$url."filemanager.js\" ".
			"defer=\"defer\"></script>";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	public static function addCSS(&$css = null) : bool
	{
		return true;
	}

	public static function doIt(&$html = null) : bool
	{
		return true;
	}
}
