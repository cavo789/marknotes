<?php
/**
 * Add a file manager button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class FileManager extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.filemanager';
	protected static $json_linked = 'plugins.page.html.filemanager';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['utility'][] = self::button(
			array(
				'name' => 'filemanager',
				'title' => 'file_manager',
				'default' => 'File manager',
				'id' => 'icon_filemanager',
				'icon' => 'folder-open',
				'task' => 'fnPluginButtonFileManager'
			)
		);

		return true;
	}

	protected static function canAdd() : bool
	{
		if ($bReturn = parent::canAdd()) {
			// We can continue
			// The file manager button will only appears if the
			// user is authenticated
			$aeSession = \MarkNotes\Session::getInstance();
			$bReturn = boolval($aeSession->get('authenticated', 0));
		}

		return $bReturn;
	}
}
