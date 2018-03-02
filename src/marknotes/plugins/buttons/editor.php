<?php
/**
 * Add an edit button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Editor extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.editor';
	protected static $json_linked = 'plugins.page.html.editor';

	public static function add(&$buttons = array()) : bool
	{
		// Get the button HTML code
		$buttons['utility'][] = self::button(
			array(
				'name' => 'editor',
				'title' => 'edit_file',
				'default' => 'Edit',
				'id' => 'icon_edit',
				'icon' => 'pencil-square-o',
				'task' => 'fnPluginButtonEdit'
			)
		);
		/*
		$aeSettings = \MarkNotes\Settings::getInstance();
		$title = $aeSettings->getText('edit_file', 'Edit', true);

		$aeSession = \MarkNotes\Session::getInstance();
		$file = $aeSession->get('filename');

		$buttons .=
			'<a id="icon_edit" data-task="fnPluginButtonEdit" data-file="'.$file.'" '.
			   'title="'.$title.'" href="#">'.
				'<i class="fa fa-pencil-square-o debug" aria-hidden="true"></i>'.
			  '</a>';
*/
		return true;
	}

	protected static function canAdd() : bool
	{
		// Conversion requires that
		//    	1. the .odt file already exists OR
		//		2. the pandoc utility is present to allow the conversion
		if ($bReturn = parent::canAdd()) {
			// We can continue
			// The editor button will only appears if the
			// user is authenticated
			$aeSession = \MarkNotes\Session::getInstance();
			$bReturn = boolval($aeSession->get('authenticated', 0));
		}

		return $bReturn;
	}
}
