<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Interface extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'desktop';
	protected static $json_settings = 'interface';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		$opt = 'accent_conversion';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.$opt, $text, $arr[$opt]);

		$opt = 'can_see';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.$opt, $text, $arr[$opt]);

		$opt = 'skin';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getCombo($key.'.'.$opt, $text, $arr[$opt], 'black;black-light;blue;blue-light;green;green-light;purple;purple-ligth;red;red-light;yellow;yellow-light');

		$opt = 'logo';
		$text = self::getTranslation($key.'.'.$opt);

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$root = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$text = str_replace('%1', $root.'assets/images/', $text);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]??'marknotes.svg');

		// Name of the website
		$key = 'site_name';
		$value = self::getArray($key);
		$text = self::getTranslation($key);
		$content .= self::getText($key, $text, $value);

		return str_replace('%CONTENT%', $content, $box);
	}
}
