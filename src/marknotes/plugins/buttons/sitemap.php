<?php
/**
 * Add a Sitemap button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class SiteMap extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.sitemap';
	protected static $json_linked = '';

	public static function add(&$buttons = array()) : bool
	{
		$buttons['app'][] = self::button(
			array(
				'title' => 'sitemap',
				'default' => 'Get the sitemap',
				'task' => 'file',
				'extra' => 'data-file="sitemap.xml"',
				'id' => 'icon_sitemap',
				'icon' => 'sitemap'
			)
		);

		return true;
	}
}
