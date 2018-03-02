<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

class MN_Homepage extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'sign-in';
	protected static $json_settings = 'plugins.task.homepage';

	private static function getListOfNotes() : string
	{
		$arrFiles = array();
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$docs = $aeSettings->getFolderDocs(false);

		$args=array(&$arrFiles);
		$aeEvents->loadPlugins('task.listfiles.get');
		$aeEvents->trigger('task.listfiles.get::run', $args);

		$arrFiles = $args[0];

		$sFiles = ';';
		foreach ($arrFiles as $file) {
			$sFiles.=str_replace(DS, '/',str_replace($docs, '', $file)).';';
		}

		return rtrim($sFiles, ';');

	}
	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// --------------------------------------
		// Retrieve the list of existing notes
		$sFiles = self::getListOfNotes();
		$key = 'plugins.options.task.homepage';
		$arr = self::getArray($key);

		$opt = 'note';
		$text = self::getTranslation($key);
		$content .= self::getCombo($key.'.'.$opt, $text, $arr[$opt], $sFiles);

		return str_replace('%CONTENT%', $content, $box);
	}
}
