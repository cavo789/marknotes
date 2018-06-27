<?php
/**
 * Add a custom button that will convert the content (HTML) to markdown
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class ConvertMDButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.convertmd';
	protected static $json_options = '';
}
