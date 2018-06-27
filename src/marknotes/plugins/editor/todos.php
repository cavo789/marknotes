<?php
/**
 * Add the %TODOS% tag into the toolbar of the editor
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class TodosButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.todos';
	protected static $json_linked = 'plugins.markdown.todos';
	protected static $json_options = '';
}
