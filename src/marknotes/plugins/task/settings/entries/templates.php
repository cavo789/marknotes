<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Templates extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings = 'templates';

	public function getFormItem() : string
	{
		$key = static::$json_settings;
		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		$content = '';

		$arrFormats = array('docx','html','index','interface','pdf','remark','reveal','timeline');
		foreach ($arrFormats as $opt) {
			$text = self::getTranslation($key.'.'.$opt);
			$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);
		}

		return str_replace('%CONTENT%', $content, $box);
	}
}
