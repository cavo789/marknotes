<?php
/**
 * Add the preview button into the toolbar of the editor
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class PreviewButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.preview';
	protected static $json_options = '';
}
