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

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/mermaid/'.
			'libs/mermaid/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$url."mermaid.forest.css\">\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * The HTML was modified by plugins like beautify
	 * or jolitypo so "undo" these changes and restore
	 * the original HTML characters.
	 */
	private static function undoHTMLChanges(string &$html, string $content)
	{
		// Don't touch on the content but use a $new variable
		// to make changes and then, replace the content in the
		// full $html
		$new = $content;
		$new = str_replace('→', '-->', $new);
		$new = str_replace('&gt;', '>', $new);
		$new = str_replace('&mdash;', '--', $new);
		$new = str_replace('&rarr;', '-->', $new);
		$new = str_replace('&nbsp;', ' ', $new);
		$new = str_replace('&ndash;', '-', $new);

		// Replace the cleaned HTML in the string
		$html = str_replace($content, $new, $html);

		return;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		$return = false;

		$pattern = '/<div class="mermaid">([\s\S]*?)<\/div>/m';

		// Perhaps more than one mermaid flowchart or diagram
		if (preg_match_all($pattern, $html, $matches)) {
			// Get how many (probably just one)

			$j = count($matches[0]);
			for ($i=0; $i<$j; $i++) {
				// Extract the "mermaid" content i.e. the
				// markdown text that will be converted in
				// a chart.
				// That content is thus the portion inside
				// <div class="mermaid">CONTENT</div>
				$content = $matches[1][$i];
				self::undoHTMLChanges($html, $content);
			}

			// A priori just for a demo : when the mermaid
			// code has been put in a
			//
			// ```mermaid
			// THE CONTENT
			// ```
			//
			// In this case "```mermaid" has been converted into
			// <code class="language-mermaid">...</code> but,
			// there too, the HTML shouldn't be modified

			$pattern = '/<code class="language-mermaid">([\s\S]*?)<\/code>/m';

			if (preg_match_all($pattern, $html, $matches)) {
				$j = count($matches[0]);
				for ($i=0; $i<$j; $i++) {
					$content = $matches[1][$i];
					self::undoHTMLChanges($html, $content);
				}
			}

			// Yes, the mermaid plugin should be enabled since
			// we've found at least one chart
			$return = true;
		}

		return $return;
	}
}
