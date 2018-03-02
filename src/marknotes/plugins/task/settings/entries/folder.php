<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Folder extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'folder';
	protected static $json_settings = 'folder';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		// Exception : this isn't an array but just a value.
		$value = self::getArray();
		$box = self::getBox($key, self::$icon);

		$opt = 'folder';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getText($key.$opt, $text, $value);

		return str_replace('%CONTENT%', $content, $box);
	}
}
