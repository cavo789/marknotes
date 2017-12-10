<?php

/**
 * Add a Copy HTML source code button into the toolbar
 */

namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class CopyHTML extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.copyhtml';
	protected static $json_linked = 'plugins.page.html.copyhtml';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['clipboard'][] = self::button(
			array(
				'name' => 'copyhtml',
				'title' => 'copy_html',
				'default' => 'Copy the HTML of the note in the clipboard',
				'icon' => 'code',
				'task' => 'fnPluginButtonCopyHTML'
			)
		);

		return true;
	}
}
