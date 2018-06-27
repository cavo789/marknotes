<?php
/**
 * Add the Include button into the toolbar of the editor
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class IncludeButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.include';
	protected static $json_linked = 'plugins.markdown.include';
	protected static $json_options = '';
}
