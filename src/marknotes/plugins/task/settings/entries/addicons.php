<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Addicons extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings =  'plugins.page.html.add_icons';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// position
		$key = 'plugins.options.page.html.add_icons';
		$arr = self::getArray($key);
		$opt = 'position';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getCombo($key.'.'.$opt, $text, $arr[$opt], 'after;before');

		return str_replace('%CONTENT%', $content, $box);
	}
}
