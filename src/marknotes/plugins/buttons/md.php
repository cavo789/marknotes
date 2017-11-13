<?php
/**
 * Add a MD export button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class MD extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.md';
	protected static $json_linked = 'plugins.page.html.md';

	private static $layout = 'md';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['export'][] = self::button(
			array(
				'name' => 'md',
				'title' => 'export_md',
				'default' => 'Export the note as a MD document',
				'id' => 'icon_md',
				'icon' => 'file-text-o',
				'task' => 'fnPluginHTMLMD'
			)
		);
		return true;
	}
}
