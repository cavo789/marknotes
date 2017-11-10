<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes\FileType;

defined('_MARKNOTES') or die('No direct access allowed');

class HTML
{
	protected static $hInstance =   null;
	private $_aeSettings = null;

	public function __construct()
	{
		$this->_aeSettings = \MarkNotes\Settings::getInstance();

		return true;
	}

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new HTML();
		}

		return self::$hInstance;
	}

	public function getHeadingText(string $html, string $heading = 'h1') : string
	{
		// Try to find a heading 1 and if so use that text for the title tag of the generated page
		$matches = array();
		$title = '';

		try {
			preg_match_all('/<'.$heading.'[^>]*>(.*)<\/'.$heading.'>/', $html, $matches);
			if (count($matches[1]) > 0) {
				$title = ((count($matches) > 0)?rtrim(@$matches[1][0]):'');
			}
		} catch (Exception $e) {
		}

		return $title;
	}

	/**
	 * Scan the $html string and add an id to each h2 and h3 tags.
	 * Used when the note is displayed as an html page.
	 *
	 * If $addGoTop is set on true, add also an icon for going back to the top
	 * of the page
	 */
	public function addHeadingsID(string $html, bool $addGoTop = false) : string
	{
		/* Create a table of content.  Loop each h2 and h3 and
		 * add an "id" like "h2_1", "h2_2", ... that will then
		 * be used in javascript
		 * (see https://css-tricks.com/automatic-table-of-contents/)
		 */

		$aeFunctions = \MarkNotes\Functions::getInstance();

		try {
			// Retrieve headings
			$matches = array();
			preg_match_all('|<h\d{1}[^>]?>(.*)</h\d{1}[^>]?>|iU', $html, $matches);

			// $matches contains the list of titles (including the tag so f.i. "<h2>Title</h2>"
			foreach ($matches[0] as $tmp) {
				// In order to have nice URLs, extract the title (stored in $tmp)
				// $tmp is equal, f.i., to <h2>My slide title</h2>
				$id = $aeFunctions->slugify(strip_tags($tmp));

				// The ID can't start with a figure, remove it if any
				// Remove also . - , ; if present at the beginning of the id
				$id = preg_replace("/^[\d|.|\-|,|;]+/", "", $id);

				// The tag (like h2)
				$head = substr($tmp, 1, 2);

				$html = str_replace($tmp, '<'.$head.' id="'.$id.'">'.strip_tags($tmp).'</'.$head.'>', $html);
			}
		} catch (Exception $e) {
		} // try

		return $html;
	}

	/**
	 * Return variables from the template file and append the html content
	 */
	public function replaceVariables(string $template, string $html, array $params = null) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();

		// The template can contains variables so call the variables
		// plugins to translate them
		// (the markdown.variables can be called even if, here, the
		// content is a HTML string)
		$aeEvents->loadPlugins('markdown.variables');
		$tmp = array('markdown'=>$template, 'filename'=>$params['filename']);
		$args = array(&$tmp);
		$aeEvents->trigger('markdown.variables::markdown.read', $args);
		$template = $args[0]['markdown'];

		// Now add note's content
		$template = str_replace('%TITLE%', $this->getHeadingText($html), $template);
		$template = str_replace('%CONTENT%', $html, $template);

		// Customization of the interface
		$interface = $aeSettings->getPlugins('/interface');

		$skin = $interface['skin'] ?? array('skin'=>'blue');

		$skin = "skin-".$skin;
		$template = str_replace('%SKIN%', $skin, $template);

		$footer = $interface['footer'] ?? array('left'=>'', 'right'=>'');
		$template = str_replace('%FOOTER_LEFT%', $footer['left'], $template);
		$footer_right = $footer['right']??'';
		$template = str_replace('%FOOTER_RIGHT%', $footer['right'], $template);

		$github = $aeSettings->getPlugins('/github', array('url'=>''));
		$template = str_replace('%GITHUB%', $github['url'], $template);

		if (strpos($template, '%VERSION%') !== false) {
			$version = $aeSettings->getPackageInfo('version');
			$template = str_replace('%VERSION%', $version, $template);
		}

		return $template;
	}
}
