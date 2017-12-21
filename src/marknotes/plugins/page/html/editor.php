<?php
/**
 * Add CSS and JS for the editor
 * @link https://github.com/sparksuite/simplemde-markdown-editor
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Editor extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.editor';
	protected static $json_options = 'plugins.options.page.html.editor';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the options for the plugin
		$bSpellCheck = boolval(self::getOptions('spellchecker', true));

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/editor/';

		$script =
			"\n<script type=\"text/javascript\" ". "src=\"".$url."libs/simplemde-markdown-editor/simplemde.min.js\" defer=\"defer\"></script>\n";

		//if ($bSpellCheck) {
		//	$rootURL = rtrim($aeFunctions->getCurrentURL(), '/');
		//	$script .= "<script type=\"text/javascript\" ".
		//		"src=\"".$rootURL."/JavaScriptSpellCheck/include.js\">".
		//		"</script>\n";
		//}

		$script .= "<script type=\"text/javascript\" ".
			"src=\"".$url."editor.js\" ".
			"defer=\"defer\"></script>".
			"\n<script type=\"text/javascript\">\n".
			"marknotes.editor={};\n".
			"marknotes.editor.spellChecker=".($bSpellCheck?"true":"false").";\n".
			"</script>";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/editor/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". "href=\"".$url."libs/simplemde-markdown-editor/simplemde.min.css\" />\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	public static function doIt(&$html = null) : bool
	{
		return true;
	}
}
