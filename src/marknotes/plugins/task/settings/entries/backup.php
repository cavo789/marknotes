<?php

namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or die('No direct access allowed');

require_once('.plugin.php');

const IGNORE_EXTENSIONS = '7z;aiff;asf;avi;bak;eot;eot2;fla;flv;f4v;gz;jpa;m4v;mkv;mov;mp3;mp4;mpeg;mpg;ogg;ogv;otf;otf2;swf;tar;tif;ttf;ttf2;wav;woff;woff2;wma;zip';

class MN_Backup extends \MarkNotes\Plugins\Task\Settings\Entries\Plugin
{
	protected static $me = __CLASS__;
	protected static $icon = 'download';
	protected static $json_settings = 'plugins.task.backup';

	private static function getListOfFolders() : string
	{
		$arrFiles = array();
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$docs = $aeSettings->getFolderDocs(false);

		$args=array(&$arrFiles);
		$aeEvents->loadPlugins('task.listfiles.get');
		$aeEvents->trigger('task.listfiles.get::run', $args);

		// Now extract only folder name, remove duplicates
		// and sort
		$arrFiles = array_map('dirname', $arrFiles);
		$arrFiles = array_unique($arrFiles);
		sort($arrFiles);

		$arrFiles = $args[0];

		$sFolders = '';
		foreach ($arrFiles as $file) {
			$sFolders.=str_replace(DS, '/', $file).';';
		}

		return rtrim($sFolders, ';');
	}

	public function getFormItem() : string
	{
		$key = static::$json_settings;

		$arr = self::getArray();
		$box = self::getBox($key, self::$icon);

		$opt = 'enabled';
		$text = self::getTranslation($key.'.'.$opt);
		$content = self::getRadio($key.'.'.$opt, $text, $arr[$opt]);

		// folder
		$key = 'plugins.options.task.backup';
		$arr = self::getArray($key);
		$opt = 'folder';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, str_replace('"', "'", $arr[$opt]));

		// prefix
		$opt = 'prefix';
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getText($key.'.'.$opt, $text, str_replace('"', "'", $arr[$opt]));

		// ignore extensions
		$opt = 'ignore_extensions';
		$text = self::getTranslation($key.'.'.$opt);
		$value = $arr[$opt]??IGNORE_EXTENSIONS;
		$content .= self::getText($key.'.'.$opt, $text, str_replace('"', "'", $value));

		// --------------------------------------
		// Retrieve the list of existing folders under /docs
		$sFolders = self::getListOfFolders();
		$arr = self::getArray($key);

		$aeSettings = \MarkNotes\Settings::getInstance();
		$docs = rtrim($aeSettings->getFolderDocs(false), DS);

		$opt = 'default_folder';
		$value = $arr[$opt]??$docs;
		$text = self::getTranslation($key.'.'.$opt);
		$content .= self::getCombo($key.'.'.$opt, $text, $value, $sFolders);

		// max number of files to process at once
		$opt = 'max_count_files';
		$text = self::getTranslation($key.'.'.$opt);
		$value = $arr[$opt]??50;
		$content .= self::getText($key.'.'.$opt, $text, str_replace('"', "'", $value));

		// max total size (in MB) to process at once
		$opt = 'max_size_files';
		$text = self::getTranslation($key.'.'.$opt);
		$value = $arr[$opt]??10;
		$content .= self::getText($key.'.'.$opt, $text, str_replace('"', "'", $value));

		return str_replace('%CONTENT%', $content, $box);
	}
}
