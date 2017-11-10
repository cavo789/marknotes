<?php
/**
 * Add extra security on the produced content like adding the
 * rel=noopener attribute to links
 * (see https://mathiasbynens.github.io/rel-noopener/)
 * See it in action : test with https://www.whatismyreferer.com/
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Security extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.security';
	protected static $json_options = '';

	/**
	 * Modify the HTML rendering of the note
	 */
	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		// Extract any <a> tags in the HTML content
		// For instance <a href="https://www.marknotes.fr">MarkNotes</a>
		// and explode the links in an array like this :
		//
		// [0] => <a href="https://www.marknotes.fr">MarkNotes</a>  (entire tag)
		// [1] => href="https://www.marknotes.fr" (attributes of <a xxxx>)
		// [2] => MarkNotes                       (the caption)
		//
		// And add the rel="noopener" attribute and "noreferrer" for old browsers

		preg_match_all("/<a ([^\>]*)>(.*)<\/a>/siU", $content, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$content = str_replace(
				$match[0],
				'<a '.$match[1].' rel="noopener noreferrer">'.$match[2].'</a>',
				$content
			);
		}
		return true;
	}
}
