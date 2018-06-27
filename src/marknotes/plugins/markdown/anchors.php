<?php
/**
 * Add manual anchors in the content.
 *
 * Search for content like :
 *
 * 		The [syntax for markdown]{#md_syntax} is ...
 *
 * i.e. search for a text between square brackets 	[....]
 * immediatly followed by curly brackets 			{}
 * with an id starting with a # 					#id
 *
 * This will then add name in HTML like this :
 *
 * 		<a name="md_syntax">syntax for markdown</a>
 *
 * So we can make a reference later on, in standard markdown :
 *
 * 		Refer to the [syntax](md_syntax)
 */
namespace MarkNotes\Plugins\Content\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Anchors extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.anchors';
	protected static $json_options = 'plugins.options.markdown.anchors';

	private static $anchors_regex = '/\[(.*?)\]{#(.*)}/m';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($markdown = $params['markdown']) === '') {
			return true;
		}

		if (!(preg_match(static::$anchors_regex, $markdown, $match))) {
			// No anchors found
			return true;
		}

		$aeSettings = \MarkNotes\Settings::getInstance();

		// Retrieve every occurences of the pattern
		if (preg_match_all(static::$anchors_regex, $markdown, $matches)) {

			for ($i=0; $i<count($matches[0]); $i++) {

				// For instance :
				// 		"The [syntax for markdown]{#md_syntax} "
				//
				// 	$tag will be ""[syntax for markdown]{#md_syntax}""
				// 	$text will be "syntax for markdown"
				// 	$name will be "#md_syntax"
				list($tag, $text, $name) = $matches;

				$markdown = preg_replace(
					'/'.preg_quote($tag[$i]).'/',
					'<a name="'.trim($name[$i]).'" class="anchorjs-link" '.
					'aria-label="Anchor" />'.$text[$i].'',
					$markdown);
			}

			$params['markdown'] = $markdown;
		}

		return true;
	}

}
