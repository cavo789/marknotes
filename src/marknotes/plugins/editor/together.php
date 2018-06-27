<?php
/**
 * Add the multiusers button into the toolbar of the editor
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class TogetherButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.together';
	protected static $json_linked = 'plugins.page.html.together';
	protected static $json_options = '';
}
