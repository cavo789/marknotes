<?php
/**
 * This plugin will read the definitions list from settings.json and if at
 * least one is found in the markdown content, the plugin will add these
 * terms at the bottom of the note.
 *
 * This is not standard in Markdown but well with Markdown extra.
 * See https://michelf.ca/projects/php-markdown/extra/#footnotes for the syntax
 *
 * Configuration :
 * -------------
 *
 *		"plugins": {
 *			 "options": {
 *				 "markdown": {
 *					 "footnotes": {
 *						 "terms":
 *							[
 *							 	{
 *									 "pattern": "PIB",
 *									 "value": "Le PIB est ..."
 *								 }
 *							]
 *						}
 *					}
 *				}
 *			}
 *		}
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Footnotes extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.footnotes';
	protected static $json_options = 'plugins.options.markdown.footnotes';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$markdown = $params['markdown'];

		// Don't keep unwanted HTML tags
		$aeRegex = \MarkNotes\Helpers\Regex::getInstance();

		$foot_MD = '';

		$arrOptions = self::getOptions('terms', array());

		foreach ($arrOptions as $footnotes) {
			$tmp = $aeRegex->removeMarkdownCodeBlock($markdown);
			$pattern = "/(.*)(".$footnotes['pattern'].")/m";

			if (preg_match_all($pattern, $tmp, $matches)) {
				for ($i=0; $i < count($matches[0]); $i++) {
					$key = "[^".$footnotes['pattern']."]";

					$match = str_replace($matches[2][$i], $footnotes['pattern'].$key, $matches[0][$i]);
					$markdown = str_replace($matches[0][$i], $match, $markdown);

					$foot_MD .= $key.": ".$footnotes['value']."\n";
				}
			}
		}

		if (trim($foot_MD)!=='') {
			$markdown = $markdown."\n\n".$foot_MD;
		}

		$params['markdown'] = $markdown;

		return true;
	}

	/**
	 * Verify if the plugin is well needed and thus have a reason
	 * to be fired
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// This plugin is only needed when at least definition
			$arrOptions = self::getOptions('terms', array());
			$bCanRun = (count($arrOptions) > 0);
		}

		return $bCanRun;
	}
}
