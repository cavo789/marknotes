<?php

/* REQUIRES PHP 7.x AT LEAST */

namespace MarkNotes\FileType;

defined('_MARKNOTES') or die('No direct access allowed');

class HTML
{
	protected static $hInstance = null;

	public function __construct()
	{
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
		// Try to find a heading 1 and if so use that text
		// for the title tag of the generated page
		$matches = [];
		$title = '';

		try {
			preg_match_all('/<' . $heading . '[^>]*>(.*)<\/' . $heading . '>/', $html, $matches);
			if (count($matches[1]) > 0) {
				$title = ((count($matches) > 0) ? rtrim(@$matches[1][0]) : '');
			}
		} catch (Exception $e) {
		}

		return $title;
	}

	/**
	 * Scan the $html string and add an id to each h2 and h3 tags.
	 * Used when the note is displayed as an html page.
	 *
	 * If $addGoTop is set on true, add also an icon for going
	 * back to the top of the page
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
			$matches = [];
			preg_match_all('|<h\d{1}[^>]?>(.*)</h\d{1}[^>]?>|iU', $html, $matches);

			// $matches contains the list of titles (including the
			// tag so f.i. "<h2>Title</h2>"

			foreach ($matches[0] as $tmp) {
				// In order to have nice URLs, extract the title
				// (stored in $tmp)
				// $tmp is equal, f.i., to <h2>My slide title</h2>

				// Don't take the font-awesome icon when getting the ID
				$tmp = preg_replace('/:fa-.*:/', '', $tmp);

				$id = $aeFunctions->slugify(strip_tags($tmp));

				// The ID can't start with one of these chars
				$id = preg_replace("/^[.|\-|\(|\[|\{|,|;]+/", '', $id);

				// The tag (like h2)
				$head = substr($tmp, 1, 2);

				$value = '<' . $head . ' id="' . $id . '">' .
					strip_tags($tmp) . '</' . $head . '>';

				$html = str_replace($tmp, $value, $html);
			}
		} catch (Exception $e) {
		} // try

		return $html;
	}

	/**
	 * Loop any <p> element and give them an "id"
	 * This way the reader will be able to immediatly reference
	 * that paragraph from the URL, with a # anchor
	 *
	 * @param  string $html
	 * @return string
	 */
	public function addParagraphsID(string $html) : string
	{
		// Give the opportunity to the webmaster to choice his
		// prefix, use an option for this
		$aeSettings = \MarkNotes\Settings::getInstance();
		$options = 'plugins.options.page.html.anchor';
		$arr = $aeSettings->getPlugins($options);
		$prefix = trim($arr['paragraph_prefix']) ?? 'par';

		// Small cleaning in case of
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$prefix = $aeFunctions->slugify(strip_tags($prefix));

		// And add a final underscore before starting the
		// numbering
		$prefix = rtrim($prefix, '_') . '_';

		$dom = new \DOMDocument();

		$dom->preserveWhiteSpace = false;
		$dom->encoding = 'utf-8';

		// IMPORTANT !!! Add xml encoding to keep emoji f.i.
		@$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);

		$xpath = new \DOMXPath($dom);

		$arrDOM = ['p'];

		for ($i = 0; $i < count($arrDOM); ++$i) {
			$list = $xpath->query('//' . $arrDOM[$i]);
			for ($j = 0; $j < $list->length; ++$j) {
				$node = $list->item($j);
				$node->setAttribute('id', $prefix . $j);
			}
		}

		$html = $dom->saveHTML($dom->documentElement);

		// The saveHTML method here above will automatically add <html>
		// and <body> tags and we don't want them since the HTML string
		// will be injected into a template file so remove these tags
		$tags = ['html', 'body'];
		foreach ($tags as $tag) {
			$html = preg_replace('/<\\/?' . $tag . '(.|\\s)*?>/', '', $html);
		}

		return $html;
	}

	/**
	 * Return variables from the template file and append the
	 * html content
	 */
	public function replaceVariables(string $template, string $html, array $params = null) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();

		// Retrieve the URL to the website
		$url = $aeFunctions->getCurrentURL();

		// The template can contains variables so call the variables
		// plugins to translate them
		// (the markdown.variables can be called even if, here, the
		// content is a HTML string)
		$aeEvents->loadPlugins('markdown.variables');
		$tmp = ['markdown' => $template, 'filename' => $params['filename']];
		$args = [&$tmp];
		$aeEvents->trigger('markdown.variables::markdown.read', $args);
		$template = $args[0]['markdown'];

		// Now add note's content
		$template = str_replace('%TITLE%', $this->getHeadingText($html), $template);
		$template = str_replace('%CONTENT%', $html, $template);

		// Customization of the interface
		$interface = $aeSettings->getPlugins('/interface');

		$skin = $interface['skin'] ?? ['skin' => 'blue'];

		$skin = 'skin-' . $skin;
		$template = str_replace('%SKIN%', $skin, $template);

		$logo = $interface['logo'] ?? 'marknotes.svg';

		$logo = $url . 'assets/images/' . $logo;
		$template = str_replace('%LOGO%', $logo, $template);

		$footer = $interface['footer'] ?? ['left' => '', 'right' => ''];
		$template = str_replace('%FOOTER_LEFT%', $footer['left'], $template);
		$footer_right = $footer['right'] ?? '';
		$template = str_replace('%FOOTER_RIGHT%', $footer['right'], $template);

		$github = $aeSettings->getPlugins('/github', ['url' => '']);
		$template = str_replace('%GITHUB%', $github['url'], $template);

		if (strpos($template, '%SHOW_TIPS%') !== false) {
			$arr = $aeSettings->getPlugins('plugins.task.homepage', ['enabled' => 1]);
			$show_tips = boolval($arr['enabled']);
			$template = str_replace('%SHOW_TIPS%', $show_tips ? 1 : 0, $template);
		}

		if (strpos($template, '%SHOW_FAVORITES%') !== false) {
			$arr = $aeSettings->getPlugins('plugins.task.favorites', ['enabled' => 1]);
			$show_fav = boolval($arr['enabled']);
			$template = str_replace('%SHOW_FAVORITES%', $show_fav ? 1 : 0, $template);
		}

		if (strpos($template, '%VERSION%') !== false) {
			$version = $aeSettings->getPackageInfo('version');
			$template = str_replace('%VERSION%', $version, $template);
		}

		if (strpos($template, '%VERSION_URL%') !== false) {
			$node = 'plugins.options.task.update';
			$arr = $aeSettings->getPlugins($node);

			$url = $arr['version_url'];
			$template = str_replace('%VERSION_URL%', $url, $template);
		}

		return $template;
	}
}
