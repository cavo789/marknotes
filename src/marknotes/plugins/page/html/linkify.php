<?php
/**
 * For converting plain text (emails, urls, ...) into links
 * @link https://github.com/SoapBox/linkifyjs
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Linkify  extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.linkify';
	protected static $json_options = '';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/linkify/';

		$script =
			"\n<script ". "src=\"".$url."libs/linkify/linkify.min.js\" ". "defer=\"defer\"></script>\n".
			"<script ". "src=\"".$url."libs/linkify/linkify-jquery.min.js\" ".
			"defer=\"defer\"></script>\n".
			"<script src=\"".$url."linkify.js\" ".
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
