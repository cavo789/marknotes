<?php
/**
 * This plugin add support for Mermaid which is a way to
 * create flowchart and diagram in markdown.
 *
 * Detect the presence of a <div class="mermaid">...</div>
 * or ```mermaid ... ```  (converted in a <code> bloc in HTML)
 * in the HTML of the note and if this is the case, include
 * the assets of mermaid and his javascript for rendering the
 * the mermaid diagram or flowchart.
 *
 * https://github.com/knsv/mermaid
 * Documentation : https://mermaidjs.github.io/
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Mermaid extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.mermaid';
	protected static $json_options = 'plugins.options.page.html.mermaid';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeSession = \MarkNotes\Session::getInstance();
		// Not sure that the HTML has been stored in the session
		$html = $aeSession->get('html', '');

		// JS are only needed when a mermaid block is found
		// in the resulting HTML
		$pattern = '/<div class="mermaid">([\s\S]*?)<\/div>/m';

		if (($html=='') || (preg_match_all($pattern, $html, $matches))) {
			// At least one block found, load .js
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$url = rtrim($aeFunctions->getCurrentURL(), '/');
			$url .= '/marknotes/plugins/page/html/mermaid/'.
				'libs/mermaid/';

			$script = "<script type=\"text/javascript\" ".
				"src=\"".$url."mermaid.min.js\" ". "defer=\"defer\"></script>\n".
				"\n<script type=\"text/javascript\" defer=\"defer\">\n".
				"$('document').ready(function(){mermaid.initialize({startOnLoad:true});;});\n".
				"</script>\n";

			$js .= $aeFunctions->addJavascriptInline($script);
		}
		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeSession = \MarkNotes\Session::getInstance();
		// Not sure that the HTML has been stored in the session
		$html = $aeSession->get('html', '');

		// CSS are only needed when a mermaid block is found
		// in the resulting HTML
		$pattern = '/<div class="mermaid">([\s\S]*?)<\/div>/m';

		if (($html=='') || (preg_match_all($pattern, $html, $matches))) {
			// At least one block found, load .css
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$theme = trim(self::getOptions('theme', 'forest'));

			if ($theme!=='') {
				$theme = '.'.$theme;
			}

			$url = rtrim($aeFunctions->getCurrentURL(), '/');
			$url .= '/marknotes/plugins/page/html/mermaid/'.
				'libs/mermaid/';

			$script =
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
				"href=\"".$url."mermaid".$theme.".css\">\n";

			$css .= $aeFunctions->addStyleInline($script);
		}

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
