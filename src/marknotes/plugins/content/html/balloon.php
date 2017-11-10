<?php
/**
 * Replace the standard 'title' attribute (i.e. the small standard tooltip)
 * by Balloon.css
 *
 * 	"plugins": {
 * 		"options": {
 *			"content": {
 *	 			"html": {
 *	 				"balloon": {
 *						 "attributes": "data-balloon-length=\"xlarge\" style=\"    text-decoration:underline dotted;\" data-balloon-pos=\"up\""
 * 					}
 * 				}
 * 			}
 * 		}
 * 	}
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Balloon extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.balloon';
	protected static $json_options = 'plugins.options.content.html.balloon';

	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		$attributes = trim(self::getOptions('attributes', 'data-balloon-pos="up"'));

		$content = str_replace('title=', $attributes. ' data-balloon=', $content);

		return true;
	}
}
