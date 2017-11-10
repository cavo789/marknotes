<?php
/**
 * This plugin will read the abbreviations list from settings.json and if at
 * least one is found in the markdown content, the plugin will add these
 * abbreviations (only found ones) at the top of the markdown rendering.
 *
 * This is not standard in Markdown but well with Markdown extra.
 * See https://michelf.ca/projects/php-markdown/extra/#abbr for the syntax
 *
 * Configuration :
 * -------------
 *
 *		"plugins": {
 *			"options": {
 *				"markdown": {
 *					"abbreviations": {
 *						"abbr": [
 *							{
 *								"pattern": "marknotes",
 *								"value": "marknotes is a software ..."
 *							},
 *							{
 *								"pattern": "joomla",
 *								"value": "Joomla!\u00ae is an OpenSource CMS ..."
 *							}
 *						]
 *					}
 *				}
 *			}
 *		}
 */
namespace MarkNotes\Plugins\Content\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Abbreviations extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.abbreviations';
	protected static $json_options = 'plugins.options.markdown.abbreviations';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$arrOptions = self::getOptions('abbr', array());

		$abbr_MD = '';

		foreach ($arrOptions as $abbreviations) {
			if (($wPos=stripos($params['markdown'], $abbreviations['pattern'])) !== false) {
				// Due to the stripos (insensitive search), derive the key
				// i.e. the found pattern but with the case ("Plugin",
				// "plugin", "PLUGIN", ...) from the note and not the pattern itself
				$key = substr($params['markdown'], $wPos, strlen($abbreviations['pattern']));
				$abbr_MD .= "*[".$key."]: ".$abbreviations['value']."\n";
			}
		}

		if (trim($abbr_MD)!=='') {
			$params['markdown'] = $abbr_MD.$params['markdown'];
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
			// This plugin is only needed when at least one abbreviation
			$arrOptions = self::getOptions('abbr', array());
			$bCanRun = (count($arrOptions) > 0);
		}

		return $bCanRun;
	}
}
