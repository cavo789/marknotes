<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Homepage extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'sign-in';
	protected static $json_settings = 'plugins.task.homepage';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);
		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$key = 'plugins.options.task.homepage';
		$arr = self::getArray($key);

		$opt = 'note';
		$text = self::getTranslation($key);
		$content .= self::getText($key, $text, $arr[$opt]);

		return str_replace('%CONTENT%', $content, $box);
	}
}
