<?php
/**
 * Allow to add extra-attributes to DOM elements
 *
 * 	"plugins": {
 * 		"options": {
 *			"content": {
 *	 			"html": {
 *	 				"attributes": {
 *						 "dom_elements": "a, code, div, h1, h2, h3, h4, h5, h6, img, p, span"
 * 					}
 * 				}
 * 			}
 * 		}
 * 	}
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Attributes extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.attributes';
	protected static $json_options = 'plugins.options.content.html.attributes';

	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		$DOM = self::getOptions('dom_elements', 'a, code, div, h1, h2, h3, h4, h5, h6, img, p, span');

		$arrDOM = explode(',', $DOM);

		$DOM_Start = '';
		$DOM_End = '';

		foreach ($arrDOM as $DOM) {
			$DOM_Start .= '<'.trim($DOM).'|';
			$DOM_End .= '<\\/'.trim($DOM).'>|';
		}

		$DOM_Start = '('.rtrim($DOM_Start, '|').')';
		$DOM_End = '(>|'.rtrim($DOM_End, '|').')';

		$regex =
			// HTML tags for which an extra attribute is allowed
			'/'.$DOM_Start.
			// The tag can be empty (immediatly followed by > or have a space and followed by f.i. attributes)
			'(>| [^>]*>)'.
			// The content (what is inside the tag)
			'(.*?)'.
			// The extra attribute is written inside brackets like {.....}
			'\\{(.*?)\\} *'.
			// The HTML end tags
			$DOM_End.'/';

		$matches = array();

		if (preg_match_all($regex, $content, $matches)) {
			for ($i=0; $i<count($matches[0]); $i++) {
				// Full match
				// <h1 class="mySuperTitle">Title{style="font-weight:bold;"}</h1>
				$tag = $matches[0][$i];
				// The HTML tag f.i. "<h1"
				$html_tag_before = $matches[1][$i];
				// "class="mySuperTitle">"
				$html_tag_before_end = $matches[2][$i];
				// What is inside the tag i.e. "title"
				$tag_content = $matches[3][$i];
				// What is inside the brackets (without the brackets) "style="font-weight:bold;""
				$attr = $matches[4][$i];
				// The HTML end tag "</h1>"
				$html_tag_end = $matches[5][$i];

				// Set $attr = '' to not put that extra attribute; f.i. when task.export.text
				$new =
					$html_tag_before.' '.$attr.$html_tag_before_end.
					$tag_content.
					$html_tag_end;

				$content = str_replace($tag, $new, $content);
			}
		}

		return true;
	}
}
