<?php
/**
 * Add <span itemprop="keyword">...</span> to word of the note.
 * Use the list of keywords as defined in settings.json like this :
 *
 *	"plugins": {
 *		"options": {
 *			"microdata": {
 *				"keywords": [
 *					 "Christophe Avonture",
 *					 "marknotes"
 *				]
 *			}
 *		}
 *	}
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Microdataextends extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.microdata';
	protected static $json_options = JSON_OPTIONS_MICRODATA;

	/**
	 * Modify the HTML rendering of the note
	 */
	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		// Don't keep unwanted HTML tags
		$arrNotIn = self::getOptions('not_in_tags', array('a','abbr'));
		$aeRegex = \MarkNotes\Helpers\Regex::getInstance();
		$tmp = $aeRegex->removeTags($content, $arrNotIn);

		// Get the list of keywords
		$arrOptions = self::getOptions('keywords', array());

		foreach ($arrOptions as $key) {
			// Search f.i. "marknotes" (the "$key") and add the
			// itemprop="keywords" attribute to enforce SEO on
			// this word
			$replacement = '<span class="microdata"><span itemscope itemtype="http://schema.org/Article"><span itemprop="keywords">'.$key.'</span></span></span>';

			$regex = $aeRegex->notInsideATag(preg_quote($key));

			if (preg_match_all($regex, $tmp, $matches)) {
				for ($i = 0; $i < count($matches[0]); $i++) {
					$value = str_ireplace($key, $replacement, $matches[0][$i]);
					$content = str_ireplace($matches[0][$i], $value, $content);
				}
			}
		}

		return true;
	}

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// This plugin is only needed when at least one folder
			// has been protected
			$arrOptions = self::getOptions('keywords', array());
			$bCanRun = (count($arrOptions) > 0);
		}

		return $bCanRun;
	}
}
