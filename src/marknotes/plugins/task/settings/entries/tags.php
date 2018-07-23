<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Tags extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings = 'plugins.content.html.tags';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// -----------------
		// Options
		$key = 'plugins.options.task.tags';
		$arr = self::getArray($key);

		$opt = 'min_chars';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		$opt = 'max_tags';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		return str_replace('%CONTENT%', $content, $box);
	}
}
