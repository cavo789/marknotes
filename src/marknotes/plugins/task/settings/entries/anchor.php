<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Anchor extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings =  'plugins.page.html.anchor';

	public function getFormItem() : string
	{
		$key = static::$json_settings;
		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// prefix
		$key = 'plugins.options.page.html.anchor';
		$arr = self::getArray($key);
		$opt = 'paragraph_prefix';
		$value = $arr[$opt]??'par';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $value);

		return str_replace('%CONTENT%', $content, $box);
	}
}
