<?php
/**
 * This plugin will display an anchor icon after each headings so, clicking on
 * that icon will put the anchor name in the URL for easy reference
 * (like page.html#the-title-name)
 *
 * Based on anchor-js; https://ben.balter.com/2014/03/13/pages-anchor-links/
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Anchor extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.anchor';
	protected static $json_options = 'plugins.options.page.html.anchor';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url .= '/marknotes/plugins/page/html/anchor/';

		$script = "<script type=\"text/javascript\" ".
			"src=\"".$url."libs/anchor-js/anchor.min.js\" ". "defer=\"defer\"></script>\n".
			"\n<script type=\"text/javascript\" defer=\"defer\">\n".
			"$('document').ready(function(){anchors.add('h2, h3, h4, h5, h6');});\n".
			"</script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url .= '/marknotes/plugins/page/html/anchor/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$url."anchor.css\">\n";

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
