<?php
/**
 * Add a reveal export button into the toolbar
 */

namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Reveal extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.reveal';
	protected static $json_linked = 'plugins.page.html.reveal';

	private static $ext = 'reveal';

	public static function add(&$buttons = array()) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$intro = $aeSettings->getText('intro_js_reveal','intro_js_reveal');
		// Get the button HTML code
		$buttons['slideshow'][] = self::button(
			array(
				'name' => 'reveal',
				'title' => 'export_reveal',
				'default' => 'slideshow',
				'id' => 'icon_reveal',
				'icon' => 'desktop',
				'task' => 'fnPluginHTMLReveal',
				'intro' => $intro
			)
		);

		return true;
	}
}
