<?php
/**
 * Add a Show favorites button
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Favorites extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.favorites';
	protected static $json_linked = '';

	public static function add(&$buttons = array()) : bool
	{
		$buttons['app'][] = self::button(
			array(
				'name' => 'favorites',
				'title' => 'favorites_show',
				'default' => 'Show yours favorites',
				'task' => 'fnPluginHTMLFavoritesShow',
				'id' => 'icon_favorites_show',
				'icon' => 'star'
			)
		);

		return true;
	}
}
