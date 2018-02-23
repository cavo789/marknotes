<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Robots extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings = 'plugins.page.html.robots';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$key = 'plugins.options.page.html.robots.bots';
		$arr = self::getArray($key);

		foreach ($arr as $bot) {
			// Only process the "robots" entry (which means
			// "all bots")
			// TODO In the future, allow to manage severall
			// bots, settings.json is already foresee for this
			if (is_array($bot)) {
				if ($bot['name']=='robots') {
					$opt = 'content';
					$text = self::getTranslation($key.'.'.$opt);
					$content .= self::getCombo($key.'.'.$opt, $text, $bot['content'], 'Index, Follow;No index, follow;Index, Nofollow;No index, no follow');
				}
			}
		}

		return str_replace('%CONTENT%', $content, $box);
	}
}
