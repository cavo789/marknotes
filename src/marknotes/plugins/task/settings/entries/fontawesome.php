<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Fontawesome extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings = 'plugins.content.html.font-awesome';

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
		$key = 'plugins.options.content.html.font-awesome';
		$arr = self::getArray($key);

		$opt = 'bullet';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, str_replace('"', "'", $arr[$opt]));

		$opt = 'extra_attribute';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, str_replace('"', "'", $arr[$opt]));

		return str_replace('%CONTENT%', $content, $box);
	}
}
