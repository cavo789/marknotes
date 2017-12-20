<?php

namespace MarkNotes\Plugins\Task\Fetch\Helpers;

defined('_MARKNOTES') or die('No direct access allowed');

class CleanHTML
{
	private $_arrContentDOM = array();
	private $_arrRemoveDOM = array();
	private $_arrRemoveAttribs = array();
	private $_arrRegex = array();

	private $html = '';
	private $source_url = '';

	public function __construct(string $html, string $source_url)
	{
		$this->html = $html;
		$this->source_url = $source_url;

		// Initialization
		$this->_arrContentDOM = array();
		$this->_arrRemoveDOM = array();
		$this->_arrRemoveAttribs = array();
		$this->_arrRegex = array();

		return true;
	}

	/**
	 * The regex entry of plugins->options->task->fetch
	 * contains search&replace expression for the content
	 * f.i. Search a specific content and replace it by
	 * a new value
	 */
	public function setRegex($arr) {
		$this->_arrRegex = $arr;
	}

	/**
	 * List of nodes where the content is placed.
	 * That list will allow to faster retrieved desired
	 * content and not pollute content by additionnal
	 * elements like comments, navigation, ...
	 */
	public function setContentDOM($arr) {
		$this->_arrContentDOM = $arr;
	}

	/**
	 * List of nodes that can be removed since they are not
	 * part of the content we want to keep
	 */
	public function setRemoveDOM($arr) {
		$this->_arrRemoveDOM = $arr;
	}

	/**
	 * List of attributes that can be removed from html tags
	 * once the desired content is isolated they are not part
	 * of the content we want to keep
	 */
	public function setRemoveAttributes($arr) {
		$this->_arrRemoveAttribs = $arr;
	}

	/**
	 * The HTML string contains the full source code (with head,
	 * body, footer, ...) and also with a lot of things like
	 * the navigation page, an aside block and so on.
	 *
	 * But the content is perhaps stored in a <article> tag or
	 * or in a <div class="article"> or ...
	 *
	 * The objective of this function is to "target" only the
	 * desired block and don't keep unneeded ones
	 */
	private function keepDOMElements(string $selector)
	{
		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
		}
		/*<!-- endbuild -->*/

		$dom = new \DOMDocument();

		$dom->encoding = 'utf-8';

		// Don't worry about spaces
		$dom->preserveWhiteSpace = false;

		// Load the HTML content
		@$dom->loadHTML(utf8_decode($this->html));

		// We'll search DOM elements
		$xpath = new \DOMXPath($dom);

		// If we find an element, got it ! We can keep it and
		// update the html string to only that part.
		foreach ($xpath->query($selector) as $e) {

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log("Keep Content - Selector [".
					$selector."] found","debug");
			}
			/*<!-- endbuild -->*/

			$this->html = $dom->saveHTML($e);
		} //foreach

		return;

	}

	/**
	 * Remove unneeded attributes in HTML tags; remove f.i.
	 * style, class, title, ... based on $_arrRemoveAttribs
	 */
	private function removeDOMAttributes(string $attrib)
	{
		$dom = new \DOMDocument;

		$dom->encoding = 'utf-8';

		// Don't worry about spaces
		$dom->preserveWhiteSpace = false;

		@$dom->loadHTML(utf8_decode($this->html));

		$xpath = new \DOMXPath($dom);

		// Find elements with a style attribute
		$nodes = $xpath->query('//*[@'.$attrib.']');

		foreach ($nodes as $node) {
			$node->removeAttribute($attrib);
		}

		$this->html = $dom->saveHTML();

		return;
	}

	/*
	* Remove all empty tags (remove <p></p>, <div></div>, ... f.i.)
	* @Link https://stackoverflow.com/a/10818570/1065340
	*/
	private function removeEmptyDOMElement()
	{
		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log("Remove empty DOM elements","debug");
		}
		/*<!-- endbuild -->*/

		$dom = new \DOMDocument;

		$dom->preserveWhiteSpace = false;
		$dom->encoding = 'utf-8';

		@$dom->loadHTML(utf8_decode($this->html));

		$xpath = new \DOMXPath($dom);

		// Check against the following attributes and if empty
		// remove them
		$arrRemove = array('div', 'p', 'span');

		for($i=0; $i<count($arrRemove); ++$i) {
			$list = $xpath->query("//".$arrRemove[$i]);
			for($j=0; $j<$list->length; ++$j) {
				$node = $list->item($j);
				if ( (!$node->hasChildNodes() ) && (trim($node->nodeValue)=='')) {
					$node->parentNode->removeChild($node);
				}
			}
		}

		$this->html = $dom->saveHTML($dom->documentElement);
	}

	private function removeDOMElements(string $selector)
	{
		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
		}
		/*<!-- endbuild -->*/

		$dom = new \DOMDocument();

		$dom->encoding = 'utf-8';

		// Don't worry about spaces
		$dom->preserveWhiteSpace = false;

		// Load the HTML content
		@$dom->loadHTML(utf8_decode($this->html));

		// We'll search DOM elements
		$xpath = new \DOMXPath($dom);

		// For each elements that we can found; just remove it
		foreach ($xpath->query($selector) as $e) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log("Remove node - Selector [".
					$selector."] found","debug");
			}
			/*<!-- endbuild -->*/

			// Delete this node
			$e->parentNode->removeChild($e);
		}

		$this->html = $dom->saveHTML($dom->documentElement);
	}

	/**
	 * Make the src image link absolute
	 * Convert /image/img.jpg to https://website/image/img.jpg
	 *
	 * $rel  = relative link to the image (from the img src attribute)
	 * $base = base URL, URL of the page.
	 *
	 * @link https://stackoverflow.com/a/5653947
	 */
	private function makeImgURLAbsolute(string $rel, string $base) : string
	{
		if (parse_url($rel, PHP_URL_SCHEME) != '') {
			return $rel;
		}

		if ($rel[0]=='#' || $rel[0]=='?') {
			return $base.$rel;
		}

		extract(parse_url($base));

		$path = preg_replace('#/[^/]*$#', '', $path);

		if ($rel[0] == '/') {
			$path = '';
		}

		$abs = "$host$path/$rel";
		$re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');

		for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {
		}

		return $scheme.'://'.$abs;
	}

	/**
	 * Process every img tags, analyze the src attribute and
	 * make img link absolute
	 */
	private function updateDOMImages()
	{
		$dom = new \DOMDocument();

		$dom->encoding = 'utf-8';

		// Don't worry about spaces
		$dom->preserveWhiteSpace = false;

		// Load the HTML content
		@$dom->loadHTML(utf8_decode($this->html));

		// We'll search DOM elements
		$xpath = new \DOMXPath($dom);

		// Process every img and specifically his src attribute
		$images = $xpath->query('//img/@src');

		foreach ($images as $img) {
			// Get the src
			$src = $img->value;

			$src = self::makeImgURLAbsolute($src,$this->source_url);

			// Set the img source absolute (with the full http://)
			$img->value = $src;
		}

		$this->html = $dom->saveHTML($dom->documentElement);

	}

	private function processRegex()
	{
		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
		}
		/*<!-- endbuild -->*/

		foreach ($this->_arrRegex as $regex) {
			// Retrieve the regex pattern for the search
			$search = $regex['search'];

			if (isset($regex['replace_by'])) {
				$replace = $regex['replace_by'];

				if (preg_match_all($search, $this->html, $matches)) {
					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug->log("Regex - Search for ".$search.
							" and replace by [".$replace."]","debug");
					}
					/*<!-- endbuild -->*/
				} // if (preg_match_all

				foreach($matches as $match) {
					$this->html = str_replace($match, $replace, $this->html);
				}
			}
		} // foreach ($this->_arrRegex as $regex)

		return;
	}

	/**
	 * h1, h2, ... should be on a new line; can't follow
	 * other elements and the text should follow the start
	 * tag (i.e. "<h2>TEXT"   and not "<h2>   (can be CRLF) text"
	 */
	private function updateHeadings() {
		// Match all headings
		$regex = '~<h[1-6]>.*<\/h[1-6]>~im';
		$this->html = preg_replace($regex, "\n\n$0",
			$this->html);

		// No space after the tag (match '<h2>   TEXT'
		// and replace by '<h2>TEXT')
		$regex = '~(<h[1-6^>]+>)(\s*)~im';
		$this->html = preg_replace($regex, "$1",
			$this->html);

		// No space after the text (match '<h2>TEXT    </h2>'
		// and replace by '<h2>TEXT</h2>')
		$regex = '~(<h[1-6^>]+>)(.*)(\s*)(</h[1-6^>]+>)~im';
		if (preg_match_all($regex, $this->html, $matches)) {
			$j = count($matches);

			for ($i=0; $i<$j; $i++) {
				// Remove end-of-line and other spaces
				if (isset($matches[1][$i])) {
					$start_tag = trim($matches[1][$i]);
					$content = trim($matches[2][$i]);
					$end_tag = $matches[4][$i];
					$this->html = str_replace($matches[0][$i],
						$start_tag.$content.$end_tag, $this->html);
				}
			}
		}

		return;
	}

	public function doIt() : string
	{
		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
		}
		/*<!-- endbuild -->*/

		// Remove HTML comments
		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log("Remove HTML comments","debug");
		}
		/*<!-- endbuild -->*/

		$this->html = preg_replace('/<!--(.*)-->/Uis', '', $this->html);

		// Remove inline script

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log("Remove inline script","debug");
		}
		/*<!-- endbuild -->*/

		$this->html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $this->html);

		// Target only needed content
		if (count($this->_arrContentDOM) > 0) {
			foreach ($this->_arrContentDOM as $selector) {
				self::keepDOMElements($selector);
			}
		}

		// Remove unneeded content
		// Use the $arrRemoveDOM array given during the construction
		// of this class. $arrRemoveDOM is feed from the settings.json
		// file and more specifically from the node
		// plugins->options->task->fetch->remove entry
		if (count($this->_arrRemoveDOM) > 0) {
			foreach ($this->_arrRemoveDOM as $selector) {
				self::removeDOMElements($selector);
			}
		}

		// ------------------------
		// From now, it's supposed that $this->html contains
		// only the wanted content (no more unwanted nav/span/div/...)
		//
		// Make the HTML cleaner by removed class, inline style, ids,
		// targets (for anchor). Don't remove anything since we need
		// to keep src for images, href for anchors, ...

		// Remove unneeded attributes (like class, style, id, ...)
		if (count($this->_arrRemoveAttribs) > 0) {

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log("Remove HTML tags attributes","debug");
			}
			/*<!-- endbuild -->*/

			foreach ($this->_arrRemoveAttribs as $attrib) {
				self::removeDOMAttributes($attrib);
			}
		}

		// Update the img tag, change the "src" attribute
		self::updateDOMImages();

		// Remove spaces between (not inside!!!) tags.
		// so "</div>	( a lot of space and CRLF ) <div>" will become
		// "</div><div>"
		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log("Remove spaces between tags","debug");
		}
		/*<!-- endbuild -->*/
		$this->html = preg_replace('/(\>)\s*(\<)/m', '$1$2', $this->html);

		// Remove empty tags (f.i. remove any <p></p>)
		self::removeEmptyDOMElement();

		// We're almost done.
		// Check if there were regex expressions and if so, process
		// them
		if (count($this->_arrRegex) > 0) {
			self::processRegex();
			// Perhaps do we have again empty DOM elements
			// Remove them in that case
			self::removeEmptyDOMElement();
		}

		// h1, h2, ... should be on a new line; can't follow
		// other elements and the title should follow the start tag
		self::updateHeadings();

		return $this->html;
	}

}
