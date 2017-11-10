<?php
/**
 * Add robots informations (like adding "index, follow") to pages.
 *
 * The settings.json file allows to address more than one bots like, f.i., in :
 *
 *	"robots": {
 * 		"bots": [
 *			{
 *				"name": "robots",
 *				"content": "index, follow"
 *			},
 *			{
 *				"name": "BaiduSpider",
 *				"content": "noindex, nofollow"
 *			}
 *		]
 *  }
 *
 * In that example, all bots are allowed to index and follow links except
 * the one of Baidu.
 *
 * See also https://developers.google.com/search/reference/robots_meta_tag
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Robots extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.robots';
	protected static $json_options = 'plugins.options.page.html.robots';

	public static function doIt(&$html = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrOptions = self::getOptions('bots', array());

		if ($arrOptions!==array()) {
			/* The options.page.html.robots.bots is defined like this in settings.json :
			 *
			 *  		"bots": [
			 *			{
			 *				"name": "robots",
			 *				"content": "index, follow"
			 *			},
			 *			{
			 *				"name": "BaiduSpider",
			 *				"content": "noindex, nofollow"
			 *			}
			 *		]
			 *
			 * i.e. a list of names and content. This make possible to address several
			 * bots
			 */

			if (count($arrOptions)==1) {
				// Only one bot define ?
				// We can then also set the X-Robots-Tag HTTP headers
				header("X-Robots-Tag: ".$arrOptions[0]['content'], true);
			}

			$meta="";
			foreach ($arrOptions as $key => $bot) {
				$meta.="<meta name=\"".$bot['name']."\" ".
					"content=\"".$bot['content']."\" />\n";
			}

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$meta = "\n<!-- Lines below are added by ".__FILE__."-->\n".
					trim($meta, "\n")."\n".
					"<!-- End for ".__FILE__."-->\n";
			}
			/*<!-- endbuild -->*/
		}

		// Add bots just after the <head> tag (so at the top)
		$html = str_replace('<head>', "<head>".$meta, $html);

		return true;
	}

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
		return true;
	}
}
