<?php
/**
 * Add the "Open in a new window" button into the toolbar
 */

namespace MarkNotes\Plugins\Buttons;

class NewWindow extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.new_window';
	protected static $json_linked = 'plugins.page.html.new_window';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['utility'][] = self::button(
			array(
				'title' => 'open_html',
				'default' => 'Open in a new window',
				'icon' => 'external-link',
				'task' => 'fnPluginHTMLNewWindow'
			)
		);

		return true;
	}
}
