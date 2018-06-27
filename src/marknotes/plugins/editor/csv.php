<?php
/**
 * Add a custom button that will read csv files
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class CSVButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.csv';
	protected static $json_linked = 'plugins.markdown.csv';
	protected static $json_options = '';
}
