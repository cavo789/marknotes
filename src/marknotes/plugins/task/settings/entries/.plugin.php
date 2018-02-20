<?php
/**
 * Settings - Entry parent class
 */
namespace MarkNotes\Plugins\Task\Settings\Entries;

defined('_MARKNOTES') or define('_MARKNOTES', 1);
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

class Plugin
{
	protected static $aeSettings = null;
	protected static $arr = array();

	public function __construct()
	{
		if (basename(__FILE__) !== '.plugin.php') {
			// Not for this parent object
			if (!isset(static::$me)) {
				throw new \Exception(get_class($this).' must have a $me '.
					'property and must initialize it exactly like this : "protected static $me = __CLASS__"');
			}
			// The child class should have a line like below
			// in his definition
			// protected static $json_settings = 'plugins.markdown.read';
			if (!isset(static::$json_settings)) {
				throw new \Exception(get_class($this).' must have a $json_settings '.
					'property and must initialize it (f.i. "plugins.markdown.read"). '.
					'That property indicates where in the settings.json file marknotes can '.
					'find the settings (enabled, not_if_task, only_if_task, ...) for that plugin');
			}
		}

		self::$aeSettings = \MarkNotes\Settings::getInstance();
		return;
	}

	public function init(array $arr) : bool
	{
		static::$arr = $arr;
		return true;
	}

	/**
	 * Retrieve part of the settings
	 * $info is a key like plugins.task.filemanager
	 */
	public static function getArray($info = '')
	{
		if ($info == '') {
			$info = static::$json_settings;
		}

		$tmp = static::$arr;
		// $info is f.i. plugins.task.filemanager so we first need to
		// extract the "plugins" node then "task" then "filemanager"
		// Use an array for this
		$tmpArray = explode('.', $info);

		foreach ($tmpArray as $key) {
			if (isset($tmp[$key])) {
				$tmp = $tmp[$key];
			} else {
				$sMsg = '--key '.$info.' not found in the '.
					'application\'s settings--';

				/*<!-- build:debug -->*/
				if (self::$aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log($sMsg, "debug");
					if ($aeDebug->getDevMode()) {
						$aeDebug->here(DEV_MODE_PREFIX.$sMsg, 1);
					}
				}
				/*<!-- endbuild -->*/
				return $sMsg;
			}
		}

		return $tmp;
	}

	/**
	 * Retrieve the translation for an option
	 * $key will be something like plugins.task.filemanager.enabled
	 * The key in the translation file should, then, be
	 * settings_plugins_task_filemanager_enabled
	 * (prefix "settings_" and every "." are replaced by a "_"
	 */
	public static function getTranslation(string $key) : string
	{
		$text = str_replace('.', '_', 'settings.'.$key);
		return static::$aeSettings->getText($text);
	}

	// Checkbox (radio button)
	public static function getRadio(string $key, string $caption,
		string $value) : string
	{
		$id = str_replace('.', '_', $key);

		$checked = boolval($value) ? 'checked="checked"' : '';
		$tip = ' (<small data-balloon="'.$key.'" data-balloon-pos="down"><i class="fa fa-key" aria-hidden="true"></i></small>)';

		return
			'<div class="row">'.
				'<div class="col-md-2">'.
					'<div class="ckbx-style-9">'.
						'<input type="checkbox" id="'.$id.'" '.
						'value="'.$value.'" '.$checked.' '.
						'data-task="settings" data-key="'.$key.'">'.
						'<label for="'.$id.'"></label>'.
					'</div>'.
				'</div>'.
				'<div class="col-md-10"><strong>%COUNTER%</strong>&nbsp;'.
					$caption.' '.$tip.'</div>'.
			'</div>';
	}

	/**
	 * Loop for any entries in arr, retrieve all $property
	 * (like 'enabled') and construct the On/Off button
	 */
	public static function loopBooleans(
		array $arr,
		string $prefix,
		string $property,
		string $caption
	) : string
	{
		$content = '';
		foreach ($arr as $key=>$value) {
			if (isset($value[$property])) {
				$bCurrentValue = $value[$property];

				if (isset($value['description'])) {
					$desc = $value['description'];
				} else {
					$desc = sprintf($caption, $key);
				}

				$content .= self::getRadio($prefix.'.'.$key.'.'.$property,
					$desc, $bCurrentValue);
			}
		}

		return $content;
	}

	// Combobox
	public static function getCombo(string $key, string $caption,
		string $value, string $values) : string
	{
		$id = str_replace('.', '_', $key);

		$arr = explode(';', $values);
		$items = '';
		foreach ($arr as $item) {
			$selected = ($item === $value) ? 'selected="selected"' : '';
			$items .= '<option data-value="'.$item.'" '.$selected.'>'.
				$item.'</option>';
		}

		$tip = ' (<small data-balloon="'.$key.'" data-balloon-pos="down"><i class="fa fa-key" aria-hidden="true"></i></small>)';

		return
			'<div class="row">'.
				'<div class="col-md-2">'.
					'<select style="width:96%;" '.
					'data-task="settings" data-key="'.$key.'">'.
					$items.
					'</select>'.
					'<label for="'.$id.'"></label>'.
				'</div>'.
				'<div class="col-md-10"><strong>%COUNTER%</strong>&nbsp;'.
					$caption.' '.$tip.'</div>'.
			'</div>';
	}

	// Text
	public static function getText(string $key, string $caption,  $value) : string
	{
		$id = str_replace('.', '_', $key);

		$tip = ' (<small data-balloon="'.$key.'" data-balloon-pos="down"><i class="fa fa-key" aria-hidden="true"></i></small>)';

		return
			'<div class="row">'.
				'<div class="col-md-2">'.
					'<div>'.
						'<input type="text" style="width:100%;" '.
						'id="'.$id.'" value="'.$value.'" '.
						'data-task="settings" data-key="'.$key.'">'.
						'<label for="'.$id.'"></label>'.
					'</div>'.
				'</div>'.
				'<div class="col-md-10"><strong>%COUNTER%</strong>&nbsp;'.
					$caption.' '.$tip.'</div>'.
			'</div>';
	}

	public static function getBox(string $title, string $icon='') : string
	{
		$title = str_replace('.', '_', $title);
		$title = self::getTranslation($title.'_title');

		if ($icon!=='') {
			$icon = '<i class="fa fa-'.$icon.'" '.
				'aria-hidden="true"></i>&nbsp;';
		}

		return
			'<div class="box box-success">'.
				'<div class="box-header">'.
					'<h3 id="%ID%" class="box-title">'.
					$icon.' %COUNTER% '.$title.'</h3>'.
				'</div>'.
				'<div class="box-body">'.
					'%CONTENT%'.
				'</div>'.
			'</div>';
	}

	public static function getTitle() : array
	{
		$icon = '';

		if (static::$icon!=='') {
			$icon = '<i class="fa fa-'.static::$icon.'" '.
				'aria-hidden="true"></i>&nbsp;';
		}

		$title = static::$json_settings.'_title';
		$title = self::getTranslation($title);

		return array($title, $icon);
	}
}
