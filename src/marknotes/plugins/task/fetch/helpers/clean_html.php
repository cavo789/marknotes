<?php

namespace MarkNotes\Plugins\Task\Fetch\Helpers;

defined('_MARKNOTES') or die('No direct access allowed');

class CleanHTML
{
	private $_arrRemoveDOM = array();
	private $_arrRemoveAttribs = array();

	private $html = '';

	public function __construct(string $html)
	{
		$this->html = $html;
		return true;
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
		$dom = new \DOMDocument;

		$dom->preserveWhiteSpace = false;
		$dom->encoding = 'utf-8';
		$dom->preserveWhiteSpace = false;

		@$dom->loadHTML(utf8_decode($this->html));

		$xpath = new \DOMXPath($dom);

		// Don't process these tags (an <img> tag is always an empty
		// tags since there is no </img>)
		$list = array('img');
		$expr = array();
		foreach ($list as $l) {
			$expr[] = "not(self::$l)";
		}
		$expr = implode(' and ', $expr);

		$selector = "//*[$expr and not(normalize-space())]";

		foreach ($xpath->query($selector) as $e) {
			$e->parentNode->removeChild($e);
		}

		$this->html = $dom->saveHTML($dom->documentElement);
	}

	private function removeDOMElements(string $selector)
	{
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
			// Delete this node
			$e->parentNode->removeChild($e);
		}

		$this->html = $dom->saveHTML($dom->documentElement);
	}

	/**
	 * Process every img tags, analyze the src attribute and
	 * add the %URL% variable used by Marknotes
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
			$src = ltrim($img->value, '/');

			// If the src starts with "images/", remove that part
			if (substr($src, 0, strlen('images/')) === 'images/') {
				$src = substr($src, strlen('images/'));
			}

			// Use Marknotes syntax, %URL%.images (presume
			// that images are stored in such folder
			$img->value = '%URL%.images/' . $src;
		}

		$this->html = $dom->saveHTML($dom->documentElement);

	}

	public function doIt() : string
	{
		// Remove HTML comments
		$this->html = preg_replace('/<!--(.*)-->/Uis', '', $this->html);

		// Remove inline script
		$this->html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $this->html);

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
			foreach ($this->_arrRemoveAttribs as $attrib) {
				self::removeDOMAttributes($attrib);
			}
		}

		// Update the img tag, change the "src" attribute
		self::updateDOMImages();

		// Remove spaces between (not inside!!!) tags.
		// so "</div>	( a lot of space and CRLF ) <div>" will become
		// "</div><div>"
		$this->html = preg_replace('/(\>)\s*(\<)/m', '$1$2', $this->html);

		// Remove empty tags (f.i. remove any <p></p>)
		self::removeEmptyDOMElement();

		return $this->html;
	}

}
