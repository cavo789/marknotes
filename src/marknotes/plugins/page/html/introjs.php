<?php
/**
 * Load intro-js
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Introjs extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.introjs';
	protected static $json_options = '';

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$url .= 'marknotes/plugins/page/html/introjs/';

		$script = "<link media=\"screen\" rel=\"stylesheet\" " .
			"type=\"text/css\" ".
			"href=\"".$url."libs/introjs.min.css\" />\n";
		$script .= "<style>.introjs-helperLayer {
			background-color:#ffd40f63;
		}</style>\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url.= '/marknotes/plugins/page/html/introjs/';

		$script =
			"\n<script ". "src=\"".$url."libs/intro.min.js\" ".
			"defer=\"defer\"></script>\n".
			"\n<script ". "src=\"".$url."intro.js\" ".
			"defer=\"defer\"></script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arr = array(
			'INTRO_HIDE_TOC' => 'intro_js_hide_toc',
			'INTRO_QUICKICONS' => 'intro_js_quickicons',
			'INTRO_SETTINGS_BUTTON' => 'intro_js_settings_button',
			'INTRO_TOC' => 'intro_js_toc',
			'INTRO_NOTE_H1' => 'intro_js_note_h1',
			'INTRO_HOMEPAGE' => 'intro_js_homepage',
			'INTRO_FAVORITES' => 'intro_js_favorites',
			'INTRO_LASTMOD' => 'intro_js_lastmod'
		);

		foreach ($arr as $key => $txt) {
			$text = $aeSettings->getText($txt,'');
			$html = str_replace('%'.$key.'%', $text, $html);
		}

		return true;
	}
}
