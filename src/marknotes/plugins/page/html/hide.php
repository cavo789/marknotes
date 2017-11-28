<?php
/**
 * Hide the treeview
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Hide extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.hide';
	protected static $json_options = '';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(), '/');

		$script =
			"<script type=\"text/javascript\" ". "src=\"".$root."/marknotes/plugins/page/html/hide/hide.js\" ".
			"defer=\"defer\"></script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal css
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(), '/');

		$script = "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$root."/marknotes/plugins/page/html/hide/hide.css\" />\n";

		$css .= $aeFunctions->addStyleInline($script);

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
