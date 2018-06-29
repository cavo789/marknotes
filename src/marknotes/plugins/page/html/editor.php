<?php
/**
 * Add CSS and JS for the editor
 * @link http://nhnent.github.io/tui.editor
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

		$rootURL = rtrim($aeFunctions->getCurrentURL(), '/');
		$url = $rootURL.'/marknotes/plugins/page/html/editor/';

		// load all (and there are a lot) .js files to make the
		// editor working.
		//
		// The marknotes\plugins\task\edit\form.php script will load
		// EXTRA .js files not required by tui but well during the
		// editing time.
		// We need to load the files here to make it possible to, f.i.,
		// right click on the treeview and choose "edit". If one .js
		// was missing, an error will be raised and the action won't
		// continue.

		$script = "<script src=\"".$url."editor.js\" ".
			"defer=\"defer\"></script>\n";

		// tui's dependencies
		$script .= 	"\n<script src=\"".$url.
			"libs/tui-editor/markdown-it.min.js\"></script>\n";
		$script .= "<script src=\"".$url.
			"libs/tui-editor/to-mark.min.js\"></script>\n";
		$script .= "<script src=\"".$url.
			"libs/tui-editor/tui-code-snippet.min.js\"></script>\n";
		$script .= "<script src=\"".$url.
			"libs/tui-editor/codemirror.js\"></script>\n";
			$script .= "<script src=\"".$url.
			"libs/tui-editor/highlight.pack.min.js\"></script>\n";
		$script .= 	"\n<script src=\"".$url.
			"libs/tui-editor/squire-raw.js\"></script>\n";
		$script .= "<script src=\"".$url.
			"libs/tui-editor/tui-editor-Editor.js\"></script>\n";

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
