<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_LastModified extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings = 'plugins.task.lastmodified';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// Number of files to show in the lastmod list
		$key = 'plugins.options.task.lastmodified';
		$arr = self::getArray($key);
		$opt = 'items_count';
		$text = self::getTranslation($key.'.'.$opt);
		$value = $arr[$opt]??10;
		$content .= self::getText($key.'.'.$opt, $text, str_replace('"', "'", $value));

		return str_replace('%CONTENT%', $content, $box);
	}
}
