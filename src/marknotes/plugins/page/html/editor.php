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

		// Get the options for the plugin
		$default = "'en':'English','es':'Spanish','fr':'French','it':'Italian','nl':'Dutch'";

		$sLang = trim(self::getOptions('language_to', $default));
		if ($sLang==='') {
			$sLang = $default;
		}

		// Should be a double quote and not a single
		$sLang = str_replace("'", '"', $sLang);

		$rootURL = rtrim($aeFunctions->getCurrentURL(), '/');
		$url = $rootURL.'/marknotes/plugins/page/html/editor/';

		// Add Simple Markdown Editor
		$script =
			"\n<script ". "src=\"".$url."libs/simplemde-markdown-editor/simplemde.min.js\" defer=\"defer\"></script>\n";

		// Add the dropzone library to allow image upload
		$script.="<script ".
			"src=\"".$rootURL."/marknotes/plugins/page/html/upload".
			"/libs/dropzone/dropzone.min.js\" ".
			"defer=\"defer\"></script>\n";

		$script .= "<script ".
			"src=\"".$url."editor.js\" ".
			"defer=\"defer\"></script>".
			"\n<script>\n".
			"marknotes.editor={};\n".
			"marknotes.editor.language_to={".$sLang."};\n".
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

		$rootURL = rtrim($aeFunctions->getCurrentURL(), '/');
		$url = $rootURL.'/marknotes/plugins/page/html/editor/';

		// Simple Markdown Editor
		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". "href=\"".$url."libs/simplemde-markdown-editor/simplemde.min.css\" />\n";

		// Dropzone
		$script .=
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$rootURL."/marknotes/plugins/page/html/".
			"upload/libs/dropzone/dropzone.min.css\">\n";

		$script .=
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". "href=\"".$url."editor.css\" />\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	public static function doIt(&$html = null) : bool
	{
		return true;
	}
}
