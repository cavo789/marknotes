<?php
/**
 * Add a custom button that will call the translate task and
 * get the translated content
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class TranslateButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.translate';
	protected static $json_linked = 'plugins.task.translate';
	protected static $json_options = '';
}
