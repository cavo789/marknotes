<?php
/**
 * Mindmap
 * @link https://github.com/jyruzicka/dugong
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class MindMap extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.mindmap';
	protected static $json_options = 'plugins.options.page.html.mindmap';

	/**
	 * Provide additional javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/') . '/';
		$url .= 'marknotes/plugins/page/html/mindmap/';

		$script = '<script ' .
			'src="' . $url . 'assets/dugong-min.js">' .
			"</script>\n" .
			"<script>\n" .
			"Dugong.populate('MN_mindmap');\n" .
			"$('.MN_mindmap').show();\n" .
			"</script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additional stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/') . '/';
		$url .= 'marknotes/plugins/page/html/mindmap/';

		$script = '<link media="screen" rel="stylesheet" type="text/css" ' .
			'href="' . $url . "assets/dugong.css\" />\n";

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
