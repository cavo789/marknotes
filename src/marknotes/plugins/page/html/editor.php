<?php
/**
 * Add CSS and JS for the editor
 * @link http://nhnent.github.io/tui.editor
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Editor extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.editor';
	protected static $json_options = 'plugins.options.page.html.editor';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$rootURL = rtrim($aeFunctions->getCurrentURL(), '/');
		$url = $rootURL.'/marknotes/plugins/page/html/editor/';

		// load only editor.js.
		// The marknotes\plugins\task\edit\form.php script will load
		// all other .js files since there are numberous and these .js
		// files are only neede when the editor is displayed.
		// If .js are referenced in this script, files will be always loaded
		// which will be counter-effective
		$script = "<script src=\"".$url."editor.js\" ".
			"defer=\"defer\"></script>";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		return true;
	}

	public static function doIt(&$html = null) : bool
	{
		return true;
	}
}
