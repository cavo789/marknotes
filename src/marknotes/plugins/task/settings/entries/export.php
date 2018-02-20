<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Export extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings = 'plugins.task.export';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		$content = '';

		// Buttons
		$arrButtons = array('docx','epub','md','odt','pdf','remark','reveal','txt');
		foreach ($arrButtons as $btn) {
			$key = 'plugins.buttons.'.$btn;
			$arr = self::getArray($key);

			$opt = 'enabled';
			$text = self::getTranslation($key.'.'.$opt);
			$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

			$opt = 'quickIcons';
			$text = self::getTranslation($key.'.'.$opt);
			$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);
		}

		return str_replace('%CONTENT%', $content, $box);
	}
}
