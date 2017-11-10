<?php
/**
 * Add support for font-Awesome
 *
 * 	"plugins": {
 * 		"options": {
 *			"content": {
 *	 			"html": {
 *	 				"font-awesome": {
 * 						"bullet": "fa-joomla fa-spin",
 * 						"extra_attribute": "style='color:#1a3b6b;'"
 * 					}
 * 				}
 * 			}
 * 		}
 * 	}
 *
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Font_Awesome extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.font-awesome';
	protected static $json_options = JSON_OPTIONS_FONT_AWESOME;

	/**
	 * Set the ul/li style to use Font-Awesome
	 */
	private static function setBullets(string $html) : string
	{
		// Replace <li></li> but only if they're part of a <ul></ul> i.e. don't modify <li> for <ol>
		// http://stackoverflow.com/a/4835671
		$sReturn = preg_replace_callback(
			"/(<ul>.*<\/ul>)/Ums",
			function ($ol) {
				$icon = self::getOptions('bullet', 'check');
				$extra = self::getOptions('extra_attribute', '');
				return preg_replace("/(<li(|\s*\/)>)/", "<li><i class='fa-li fa ".$icon."' ".$extra."></i>", $ol[1]);
			},
			$html
		);

		$sReturn =  str_replace('<ul>', '<ul class="fa-ul">', $sReturn);

		return $sReturn;
	}

	private static function addFont(string $html) : string
	{
		if (preg_match_all('/:(fa-[^:]*):/', $html, $matches)) {
			$i = 0;

			list($pattern, $bullet) = $matches;

			foreach ($pattern as $code) {
				$html=str_replace($code, '<i class="fa '.$bullet[$i].'" aria-hidden="true"></i>', $html);

				$i+=1;
			}
		}
		return $html;
	}

	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		$content = self::setBullets($content);
		$content = self::addFont($content);

		return true;
	}
}
