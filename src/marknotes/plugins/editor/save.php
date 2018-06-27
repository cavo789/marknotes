<?php
/**
 * Add the save button into the toolbar of the editor
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class SaveButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.save';
	protected static $json_options = '';
}
