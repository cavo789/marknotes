<?php
/**
 * This plugin will read the definitions list from settings.json and if at
 * least one is found in the markdown content, the plugin will add these
 * terms at the bottom of the note.
 *
 * This is not standard in Markdown but well with Markdown extra.
 * See https://michelf.ca/projects/php-markdown/extra/#def-list for the syntax
 *
 * Configuration :
 * -------------
 *
 *		"plugins": {
 *			"options": {
 *				"markdown": {
 *					"definitions": {
 *						"title": "---\n***Glossary***",
 *						"terms": [
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

class Definitions extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.definitions';
	protected static $json_options = 'plugins.options.markdown.definitions';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$arrOptions = self::getOptions('terms', array());

		$title = self::getOptions('title', '## Glossary');
		$def_MD = '';

		foreach ($arrOptions as $definitions) {
			// The word for the glossary
			$key = $definitions['pattern'];

			if (stripos($params['markdown'], $key) !== false) {
				$def_MD .= $definitions['pattern']."\n:   ".$definitions['value']."\n\n";
			}
		}

		if (trim($def_MD)!=='') {
			$params['markdown'] = $params['markdown']."\n\n".$title."\n\n".$def_MD;
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
			// This plugin is only needed when at least definition
			$arrOptions = self::getOptions('terms', array());
			$bCanRun = (count($arrOptions) > 0);
		}

		return $bCanRun;
	}
}
