<?php
/**
 * Add a Fullscreen button into the toolbar
 */

namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Fullscreen extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.fullscreen';
	protected static $json_linked = 'plugins.page.html.fullscreen';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['utility'][] = self::button(
			array(
				'name' => 'fullscreen',
				'title' => 'fullscreen',
				'default' => 'Display the note in fullscreen',
				'task' => 'fnPluginHTMLFullScreen',
				'icon' => 'arrows-alt'
			)
		);

		return true;
	}
}
