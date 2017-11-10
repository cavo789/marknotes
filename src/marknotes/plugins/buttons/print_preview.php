<?php
/**
 * Add a Printer button into the toolbar
 */

namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Print_Preview extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.print_preview';
	protected static $json_linked = 'plugins.page.html.print_preview';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['utility'][] = self::button(
			array(
				'title' => 'print_preview',
				'task' => 'fnPluginHTMLPrintPreview',
				'default' => 'Print preview',
				'id' => 'icon_print_preview',
				'icon' => 'print'
			)
		);

		return true;
	}
}
