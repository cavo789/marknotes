<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Regional extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'globe';
	protected static $json_settings = 'regional';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		$opt = 'language';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getCombo($key.'.'.$opt, $text, $arr[$opt], 'fr;en');

		$opt = 'locale';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.$opt, $text, $arr[$opt]);

		$opt = 'timezone';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.$opt, $text, $arr[$opt]);

		return str_replace('%CONTENT%', $content, $box);
	}
}
