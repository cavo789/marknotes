<?php
/**
 * The update plugin will call the task.update.run task.
 * This file is needed so the .js script can be added to the DOM
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Update extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.update';
	protected static $json_options = '';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// Only when a valid user is logged on
		$bContinue = boolval($aeSession->get('authenticated', 0));

		if ($bContinue) {
			$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
			$url .= 'marknotes/plugins/page/html/update/';

			$script =
				"<script src=\"".$url."update.js\" ".
				"defer=\"defer\"></script>\n";
		} else {
			// Because fnPluginTaskUpdate() is also defined in the
			// template, we need to have that function in the HTML
			$script =
				"<script>\n".
				"function fnPluginTaskUpdate() {\n".
					"Noty({\n".
					"	message: $.i18n('not_authenticated'),\n".
					"	type: 'warning'\n".
					"});\n".
				"}\n".
				"</script>\n";
		}

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

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		return true;
	}

}
