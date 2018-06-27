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
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Mermaid extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.mermaid';
	protected static $json_options = 'plugins.options.page.html.mermaid';

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
	 public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		$pattern = '/<div class="mermaid">([\s\S]*?)<\/div>/m';

		// Perhaps more than one mermaid flowchart or diagram
		if (preg_match_all($pattern, $content, $matches)) {
			// Get how many (probably just one)

			$j = count($matches[0]);

			for ($i=0; $i<$j; $i++) {
				// Extract the "mermaid" content i.e. the
				// markdown text that will be converted in
				// a chart.
				// That content is thus the portion inside
				// <div class="mermaid">CONTENT</div>
				$mermaid = $matches[1][$i];
				self::undoHTMLChanges($content, $mermaid);
			}
		}
		// À priori just for a demo : when the mermaid
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

		if (preg_match_all($pattern, $content, $matches)) {

			$j = count($matches[0]);

			for ($i=0; $i<$j; $i++) {
				$mermaid = $matches[1][$i];
				self::undoHTMLChanges($content, $mermaid);
			}
		}

		return true;
	}
}
