<?php
/**
 * Add the LastUpdate button into the toolbar of the editor
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class LastUpdateButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.lastupdate';
	protected static $json_linked = 'plugins.markdown.lastupdate';
	protected static $json_options = '';
}
