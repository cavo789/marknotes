<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Export extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'square';
	protected static $json_settings = 'plugins.task.export';

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		$content = '';

		// Buttons
		$arrButtons = array('docx','epub','md','odt','pdf','remark','txt');
		foreach ($arrButtons as $btn) {
			$key = 'plugins.buttons.'.$btn;
			$arr = self::getArray($key);

			$opt = 'enabled';
			$text = self::getTranslation($key.'.'.$opt);
			$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

			$opt = 'quickIcons';
			$text = self::getTranslation($key.'.'.$opt);
			$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);
		}

		// --- Docx version Googoose
		$key = 'plugins.page.html.docx';
		$arr = self::getArray($key);
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// --- Docx markdown extra info
		$key = 'plugins.markdown.docx';
		$arr = self::getArray($key);
		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// --- Decktape
		$key = 'plugins.options.task.export.decktape';
		$arr = self::getArray($key);
		$opt = 'script';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		// --- Pandoc
		$key = 'plugins.options.task.export.pandoc';
		$arr = self::getArray($key);
		$opt = 'script';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);

		// --- Pandoc - options
		$key = 'plugins.options.task.export.pandoc.options';
		$arr = self::getArray($key);

		$arrFormats = array('docx','epub','odt','pdf','txt');
		foreach ($arrFormats as $opt) {
			$text = self::getTranslation($key.'.'.$opt);
			$content .= self::getText($key.'.'.$opt, $text, $arr[$opt]);
		}

		return str_replace('%CONTENT%', $content, $box);
	}
}
