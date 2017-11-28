<?php
/**
 * i18n is the internationalization plugin, should remains enabled to give the opportunity to offer content to your user in their mother languages
 * @link https://github.com/wikimedia/jquery.i18n
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class i18n extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.i18n';
	protected static $json_options = 'plugins.options.page.html.i18n';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(), '/');
		$url = $root.'/marknotes/plugins/page/html/i18n/';
		$urlLib = $url.'libs/';

		$script = "<script type=\"text/javascript\" defer=\"defer\" ".
			"src=\"".$root."/libs/js-url/url.min.js\"></script>\n".
			"<script type=\"text/javascript\" defer=\"defer\" ". "src=\"".$urlLib."CLDRPluralRuleParser/CLDRPluralRuleParser.js\">".
			"</script>".
			"<script type=\"text/javascript\" defer=\"defer\" ". "src=\"".$urlLib."jquery.i18n/jquery.i18n.js\"></script>".
			"<script type=\"text/javascript\" defer=\"defer\" ". "src=\"".$urlLib."jquery.i18n/jquery.i18n.messagestore.js\"></script>".
			"<script type=\"text/javascript\" defer=\"defer\" ". "src=\"".$urlLib."jquery.i18n/jquery.i18n.fallbacks.js\"></script>".
			"<script type=\"text/javascript\" defer=\"defer\" ". "src=\"".$urlLib."jquery.i18n/jquery.i18n.parser.js\"></script>".
			"<script type=\"text/javascript\" defer=\"defer\" ". "src=\"".$urlLib."jquery.i18n/jquery.i18n.emitter.js\"></script>".
			"<script type=\"text/javascript\" defer=\"defer\" ". "src=\"".$urlLib."jquery.i18n/jquery.i18n.language.js\"></script>".
			"<script type=\"text/javascript\" defer=\"defer\" ".
			"src=\"".$url."i18n.js\"></script>\n";

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
