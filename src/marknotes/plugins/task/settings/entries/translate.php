<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Translate extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings = 'plugins.task.translate';

	public function getFormItem() : string
	{
		$key = static::$json_settings;
		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// Text to include below the translation
		$key = 'plugins.options.task.translate.include';
		$arr = self::getArray($key);
		$opt = 'after';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		// ----------------------------------------------
		// Google translate
		$key = 'plugins.page.html.gtranslate';
		$arr = self::getArray($key);
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$key = 'plugins.options.page.html.gtranslate';
		$arr = self::getArray($key);
		$opt = 'enable_localhost';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		return str_replace('%CONTENT%', $content, $box);
	}
}
