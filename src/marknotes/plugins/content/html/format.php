<?php
/**
 * Add <mark> tags in the HTML rendering
 * This plugin has no configuration in settings.json, you just need
 * to type ==keyword== in your markdown content and this plugin will
 * translate into <mark>keyword</mark>
 *
 *
 * 		"plugins" : {
 *			"options": {
 *				 "content": {
 *					 "html": {
 *						 "format": {
 *							 "prefix": [
 *								 {
 *									 "pattern": "==",
 *									 "value": "<mark class='animated infinite flash'>$1</mark>"
 *								 },
 *								 {
 *									 "pattern": "~~",
 *									 "value": "<del>$1</del>"
 *								 },
 *								 {
 *									 "pattern": "++",
 *									 "value": "<ins>$1</ins>"
 *								 },
 *								 {
 *									 "pattern": "##",
 *									 "value": "<kbd>$1</kbd>"
 *								 },
 *								 {
 *									 "pattern": "^^",
 *									 "value": "<sup>$1</sup>"
 *								 },
 *								 {
 *									 "pattern": "§",
 *									 "value": "<sub>$1</sub>"
 *								 }
 *							 ]
 *						 }
 *					 }
 *				}
 *			}
 *		}
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Format extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.format';
	protected static $json_options = 'plugins.options.content.html.format';

	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		// Get the options for the Format plugin
		$arr = self::getOptions('prefix', array());

		// If defined $arr will be an array with two informations; a
		// pattern and a value.
		// The pattern can be "==" and the value "<mark>$1</mark>"
		// This means : replace every word between two == and add the
		// <mark> tag before.
		// So replace "==Important part of the sentence==" by
		// "<mark>Important part of the sentence</mark>"

		if (count($arr)>0) {
			for ($i=0; $i < count($arr); $i++) {

				if (isset($arr[$i]['pattern']) && isset($arr[$i]['value'])) {

					// Get the prefix to search, for instance "=="
					$prefix = trim($arr[$i]['pattern']);

					// Get the "replace by", for instance "<mark>$1</mark>"
					$value = $arr[$i]['value'];

					$first = substr($prefix, 0, 1);

					$pattern =
						// The beginning prefix is a double ==
						preg_quote($prefix).
						// The keyword. Can't contain the pattern
						'([^'.preg_quote($prefix).']*)'.
						// The ending prefix is a double ==
						preg_quote($prefix);

					$matches = array();
					if (preg_match_all('/'.$pattern.'/m', $content, $matches)) {

						// Retrieve matches groups
						list($pattern, $keyword) = $matches;

						// Process every keywords
						for ($j=0; $j<count($keyword); $j++) {
							$key = str_replace('$1', $keyword[$j], $value);
							$content = str_replace($pattern[$j], $key, $content);
						}
					} // if (count($matches[1])>0)
				}
			} // foreach ($arr as $prefix => $value)
		} // if (count($arr)>0)

		return true;
	}
}
