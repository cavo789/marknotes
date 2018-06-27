<?php
/**
 * Add the encrypt button into the toolbar of the editor
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class EncryptButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.encrypt';
	protected static $json_linked = 'plugins.task.encrypt';
	protected static $json_options = '';
}
