<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Buttons extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings = 'plugins.buttons';

	protected static $ignore = array('clear','docx','editor','epub','favorites','filemanager','login','md','odt','pdf','remark','reveal','sitemap','timeline','txt','update');

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();

		// Those buttons will be managed by their own configuration
		// entry (f.i. the editor button)
		if (count(static::$ignore)>0) {
			foreach (static::$ignore as $btn) {
				unset($arr[$btn]);
			}
		}

		$box = self::getBox($key, self::$icon);

		$text = self::getTranslation($key.'.enabled');
		$content = self::loopBooleans($arr, $key, 'enabled', $text);

		$text = self::getTranslation($key.'.quickicons');
		$content .= self::loopBooleans($arr, $key, 'quickIcons', $text);

		return str_replace('%CONTENT%', $content, $box);
	}
}
