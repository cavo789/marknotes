<?php
/**
  * Add the last update date/time in the file
  *
  *		"plugins": {
  *			"options": {
  *				"markdown": {
  *					"lastupdate": {
  *						"text": ">*Last update : %s*"
  *					}
  *				}
  *			}
  *		}
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class LastUpdate extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.lastupdate';
	protected static $json_options = 'plugins.options.markdown.lastupdate';

	/**
	 * The markdown file has been read, this function will get the content of the .md file and
	 * make some processing like data cleansing
	 *
	 * $params is a associative array with, as entries,
	 *		* markdown : the markdown string (content of the file)
	 *		* filename : the absolute filename on disk
	 */
	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		if (strpos($params['markdown'], '%LASTUPDATE%')!==false) {
			$filename = $params['filename'];

			// Default will be '**Last update : %s**
			$text = self::getOptions('text', '**Last update : %s**');

			$aeSettings = \MarkNotes\Settings::getInstance();
			$date=utf8_encode(ucfirst(strftime($aeSettings->getText('date'), filemtime($filename))));

			$params['markdown'] = str_replace('%LASTUPDATE%', sprintf($text, $date), $params['markdown']);
		}

		return true;
	}
}
