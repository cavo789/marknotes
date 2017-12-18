<?php
/**
 * Add a Update button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Update extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.update';
	protected static $json_linked = '';

	public static function add(&$buttons = array()) : bool
	{
		$buttons['app'][] = self::button(
			array(
				'name' => 'update',
				'title' => 'update_software',
				'default' => 'Update marknotes, install a newer version',
				'task' => 'fnPluginTaskUpdate',
				'id' => 'icon_update',
				'icon' => 'cloud-download'
			)
		);

		return true;
	}
}
