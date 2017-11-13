<?php
/**
 * Add a Timeline button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Timeline extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.timeline';
	protected static $json_linked = '';

	public static function add(&$buttons = array()) : bool
	{
		$buttons['app'][] = self::button(
			array(
				'name' => 'timeline',
				'title' => 'timeline',
				'default' => 'Display notes in a timeline view',
				'task' => 'file',
				'extra' => 'data-file="timeline.html"',
				'id' => 'icon_timeline',
				'icon' => 'calendar'
			)
		);

		return true;
	}
}
