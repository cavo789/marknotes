<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Fakeloader extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings =  'plugins.page.html.fakeLoader';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		$content='';
		// DON'T ALLOW TO DISABLE THIS PLUGIN OTHERWISE
		// THE USER EXPERIENCE WILL BE BAD SINCE THE USER WILL
		// SEE THE INTERFACE DURING HIS GENERATION
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// bgColor
		$key = 'plugins.options.page.html.fakeLoader';
		$arr = self::getArray($key);
		$opt = 'bgColor';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, str_replace('"', "'", $arr[$opt]));

		$opt = 'spinner';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getCombo($key.'.'.$opt, $text, $arr[$opt], 'spinner1;spinner2;spinner3;spinner4;spinner5;spinner6;spinner7');

		$opt = 'timeToHide';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		return str_replace('%CONTENT%', $content, $box);
	}
}
