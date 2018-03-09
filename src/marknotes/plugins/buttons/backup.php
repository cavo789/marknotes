<?php
/**
 * Add a Backup button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Backup extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.backup';
	protected static $json_linked = '';

	public static function add(&$buttons = null) : bool
	{
		$buttons['app'][] = self::button(
			array(
				'name' => 'backup',
				'title' => 'button_backup',
				'task' => 'fnPluginTaskBackup',
				'icon' => 'download'
			)
		);
		return true;
	}

	/**
	 * The button can only be visible for logged-in users
	 */
	protected static function canAdd() : bool
	{
		if ($bReturn = parent::canAdd()) {
			$aeSession = \MarkNotes\Session::getInstance();
			$bReturn = boolval($aeSession->get('authenticated', 0));
		}

		return $bReturn;
	}
}
