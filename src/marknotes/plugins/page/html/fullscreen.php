<?php
/**
 * Add the fullscreen JS script
 * @link http://stackoverflow.com/a/23971798
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class FullScreen extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.fullscreen';
	protected static $json_options = '';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

		$script =
			"<script type=\"text/javascript\" ". "src=\"".$root."/marknotes/plugins/page/html/fullscreen/fullscreen.js\" ".
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

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		return true;
	}
}
