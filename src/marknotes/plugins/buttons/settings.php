<?php
/**
 * Button to get access to the settings form
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Settings extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.settings';
	protected static $json_linked = '';

	public static function add(&$buttons = array()) : bool
	{
		$buttons['app'][] = self::button(
			array(
				'name' => 'settings',
				'title' => 'update_settings',
				'default' => 'Update settings',
				'task' => 'settings',
				'extra' => 'data-key="task=show_form"',
				'icon' => 'toggle-on'
			)
		);

		return true;
	}

	protected static function canAdd() : bool
	{
		// The Settings button can't be displayed to visitors

		if ($bReturn = parent::canAdd()) {
			$aeSession = \MarkNotes\Session::getInstance();
			$bReturn = boolval($aeSession->get('authenticated', 0));
		}

		return $bReturn;
	}
}
