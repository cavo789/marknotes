<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Optimize extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'fighter-jet';
	protected static $json_settings = 'plugins.task.optimize';

	public function getFormItem() : string
	{
		$key = 'plugins.task.optimize';
		$arr = self::getArray($key);
		$box = self::getBox($key, self::$icon);

		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// Buttons
		$key = 'plugins.buttons.clear';
		$arr = self::getArray($key);
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);
		$opt = 'quickIcons';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$key = 'plugins.options.task.optimize';
		$arr = self::getArray($key);

		$opt = 'localStorage';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$opt = 'server_session';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$opt = 'cache.enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr['cache']['enabled']);

		$opt = 'duration.default';
		$text = self::getTranslation($key.'.cache.'.$opt);
		$content .= self::getText($key.'.cache.'.$opt, $text, $arr['cache']['duration']['default']);

		$opt = 'duration.html';
		$text = self::getTranslation($key.'.cache.'.$opt);
		$content .= self::getText($key.'.cache.'.$opt, $text, $arr['cache']['duration']['html']);

		$opt = 'duration.sitemap';
		$text = self::getTranslation($key.'.cache.'.$opt);
		$content .= self::getText($key.'.cache.'.$opt, $text, $arr['cache']['duration']['sitemap']);

		return str_replace('%CONTENT%', $content, $box);
	}
}
