<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Login extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'sign-in';
	protected static $json_settings = 'plugins.task.login';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// Buttons
		$key = 'plugins.buttons.login';
		$arr = self::getArray($key);
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);
		$opt = 'quickIcons';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$key = 'plugins.options.task.login';
		$arr = self::getArray($key);
		$content .= self::getText($key.'username', 'Login', $arr['username']);
		$content .= self::getText($key.'password', 'Password', $arr['password']);

		return str_replace('%CONTENT%', $content, $box);
	}
}
