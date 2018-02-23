<?php
/**
 * This plugin will read the definitions list from settings.json and
 * if at least one is found in the markdown content, the plugin will
 * add these terms at the bottom of the note.
 *
 * This is not standard in Markdown but well with Markdown extra.
 * See https://michelf.ca/projects/php-markdown/extra/#def-list
 * for the syntax
 *
 * Configuration :
 * -------------
 *
 *	"plugins": {
 *		"options": {
 *			"markdown": {
 *				"definitions": {
 *					"title": "---\n***Glossary***",
 *					"terms": [
 *						{
 *							"pattern": "marknotes",
 *							"value": "marknotes is a software ..."
 *						},
 *						{
 *							"pattern": "joomla",
 *							"value": "Joomla!\u00ae is a CMS ..."
 *						}
 *				]
 *				}
 *			}
 *		}
 *	}
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Definition extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.definition';
	protected static $json_options = 'plugins.options.markdown.definitions';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/definitions/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$url."definitions.css\">\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		// Don't keep unwanted HTML tags
		$sNotIn = self::getOptions('not_in_tags', 'a, abbr');
		$arrNotIn = explode(",", $sNotIn);
		// Remove trailing spaces
		$arrNotIn = array_map("trim", $arrNotIn);

		$aeRegex = \MarkNotes\Helpers\Regex::getInstance();
		$tmp = $aeRegex->removeTags($html, $arrNotIn);

		$arrOptions = self::getOptions('terms', array());

		foreach ($arrOptions as $definitions) {
			// The word for the glossary
			$key = $definitions['pattern'];

			// Replace a word (i.e. the "$key") in the HTML content
			// but never when that word is inside an html tag; so
			// only modify pure text, nor an attribute
			$pattern = '/(.*)('.preg_quote($key).')(.*)(?!([^<]+)?>)/i';

			if (preg_match_all($pattern, $tmp, $matches)) {
				for ($i = 0; $i < count($matches[0]); $i++) {
					$replacement = '<span class="definition">'.$matches[2][$i].'</span>';
					$value = str_ireplace($matches[2][$i], $replacement, $matches[0][$i]);
					$html = str_replace($matches[0][$i], $value, $html);
				}
			}
		}
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
