<?php
/**
 * The clipboard plugin will add two functionnalities :
 *
 *  - copy the note content (not the source but the content) in the clipboard
 *  - copy the URL of the note in the clipboard
 *
 * @link https://zenorocha.github.io/clipboard.js
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Clipboard extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.clipboard';
	protected static $json_options = '';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$url .= 'marknotes/plugins/page/html/clipboard/';

		$script =
			"<script ". "src=\"".$url."libs/clipboard-js/clipboard.min.js\" ".
			"defer=\"defer\"></script>\n".
			"<script src=\"".$url."clipboard.js\" ".
			"defer=\"defer\"></script>\n";

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
