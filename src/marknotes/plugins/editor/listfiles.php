<?php
/**
 * Add the ListFiles button into the toolbar of the editor
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class ListFilesButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.listfiles';
	protected static $json_linked = 'plugins.markdown.listfiles';
	protected static $json_options = '';
}
