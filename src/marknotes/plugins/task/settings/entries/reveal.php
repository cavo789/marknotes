<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Reveal extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings =  'plugins.page.html.reveal';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		// Enabled
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// ----------------------
		// Buttons
		$key = 'plugins.buttons.reveal';
		$arr = self::getArray($key);

		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$opt = 'quickIcons';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// ----------------------
		// Misc
		$key = 'plugins.options.page.html.reveal';
		$arr = self::getArray($key);

		$opt = 'HideUnnecessaryThings';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		$opt = 'no_html_convert';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// ----------------------
		// Animation
		$key = 'plugins.options.page.html.reveal.animation';
		$arr = self::getArray($key);

		$opt = 'bullet';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getCombo($key.'.'.$opt, $text, $arr[$opt], ';fragment');

		for ($i=1; $i<=6; $i++) {
			$opt = 'h'.$i;
			$text = self::getTranslation($key.'.'.$opt);
			$content .= self::getCombo($key.'.'.$opt, $text, $arr[$opt], 'none;concave;convex;fade;slide;random;zoom');
		}

		// ----------------------
		// Appearance
		$key = 'plugins.options.page.html.reveal.appearance';
		$arr = self::getArray($key);

		$opt = 'theme';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getCombo($key.'.'.$opt, $text, $arr[$opt], 'beige;black;blood;league;moon;night;serif;simple;sky;solarized;white');

		// ----------------------
		// Duration
		$key = 'plugins.options.page.html.reveal.duration';
		$arr = self::getArray($key);

		$opt = 'minutes';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		$opt = 'bar_height';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		return str_replace('%CONTENT%', $content, $box);
	}
}
