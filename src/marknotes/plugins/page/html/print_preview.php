<?php
/**
 * @link https://github.com/jasonday/printThis
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class PrintPreview extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.print_preview';
	protected static $json_options = '';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/print_preview/';

		$script =
			"<script type=\"text/javascript\" src=\"".$url."libs/printThis/printThis.js\" ".
			"defer=\"defer\"></script>\n".
			"<script type=\"text/javascript\" src=\"".$url."print_preview.js\" ".
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
