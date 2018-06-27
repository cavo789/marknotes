<?php
/**
 * Add a custom button that will show an upload area to make it very
 * easy to upload files by just drag&drop
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class UploadButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.upload';
	protected static $json_options = '';
}
