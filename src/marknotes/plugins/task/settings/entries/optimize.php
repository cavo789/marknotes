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

		// -------------------
		// Cache
		$key = 'plugins.options.task.optimize.cache';
		$arr = self::getArray($key);

		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$key = 'plugins.options.task.optimize.cache.duration';
		$arr = self::getArray($key);

		$opt = 'default';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		$opt = 'html';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		$opt = 'sitemap';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		// -------------------
		// HTML
		$key = 'plugins.options.task.optimize.html';
		$arr = self::getArray($key);
		$opt = 'minify';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$opt = 'remove_comments';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// CSS
		$key = 'plugins.options.task.optimize.css';
		$arr = self::getArray($key);
		$opt = 'minify';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// JS
		$key = 'plugins.options.task.optimize.js';
		$arr = self::getArray($key);
		$opt = 'minify';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// Images
		$key = 'plugins.options.task.optimize.images';
		$arr = self::getArray($key);
		$opt = 'lazyload';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// Headers
		$key = 'plugins.options.task.optimize.headers';
		$arr = self::getArray($key);
		$opt = 'browser_cache';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		return str_replace('%CONTENT%', $content, $box);
	}
}
