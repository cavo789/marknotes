<?php
/**
 * Add a ZIP button that will allow to download the note
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class ZipButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.zip';
	protected static $json_options = '';

	public static function add(&$buttons = array()) : bool
	{
		parent::add($buttons);

		// Add an external script, load jszip.min.js
		// @Link https://github.com/Stuk/jszip-utils
		//$buttons['script'][__CLASS__]='zip/libs/jszip.min.js';

		return true;
	}
}
