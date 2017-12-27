<?php
/**
 * Show the settings's form
 *
 * Answer to URL like index.php?task=task.settings.show_form
 */
namespace MarkNotes\Plugins\Task\Settings;

defined('_MARKNOTES') or die('No direct access allowed');

class Show_Form
{
	private static $chapter = 0;

	private static function getBox(string $title, string $icon='') : string
	{
		static::$chapter++;

		if ($icon!=='') {
			$icon = '<i class="fa fa-'.$icon.'" '.
				'aria-hidden="true"></i>&nbsp;';
		}

		return
			'<div class="box box-success">'.
				'<div class="box-header">'.
					'<h3 class="box-title">'.$icon.static::$chapter.'. '.$title.'</h3>'.
				'</div>'.
				'<div class="box-body">'.
					'%CONTENT%'.
				'</div>'.
			'</div>';
	}

	// Combobox
	private static function getCombo(string $key, string $caption,
		string $value, string $values) : string
	{
		$id = str_replace('.', '_', $key);

		$arr = explode(';', $values);
		$items = '';
		foreach ($arr as $item) {
			$items .= '<option data-task="settings" '.PHP_EOL.
				'data-key="'.$key.'" data-value="'.$item.'">'.PHP_EOL.
				$item.'</option>'.PHP_EOL;
		}

		return
			'<div class="row">'.PHP_EOL.
				'<div class="col-md-2">'.PHP_EOL.
					'<select class="form-control">'.PHP_EOL.
					$items.PHP_EOL.
					'</select>'.PHP_EOL.
					'<label for="'.$id.'"></label>'.PHP_EOL.
				'</div>'.PHP_EOL.
				'<div class="col-md-10">'.$caption.'</div>'.PHP_EOL.
			'</div>'.PHP_EOL;
	}

	// Checkbox (radio button)
	private static function getRadio(string $key, string $caption,
		string $value) : string
	{
		static $i = 0;
		static $oldChapter = 0;

		if (static::$chapter !== $oldChapter) {
			$i = 0;
			$oldChapter = static::$chapter;
		}

		$i++;

		$id = str_replace('.', '_', $key);

		$checked = boolval($value) ? 'checked="checked"' : '';

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
				'<div class="col-md-10"><strong>'.static::$chapter.'.'.$i.'.</strong>&nbsp;'.$caption.'</div>'.
			'</div>';
	}


	// Text
	private static function getText(string $key, string $caption,
		string $value, int $size = 35) : string
	{
		static $i = 0;
		static $oldChapter = 0;

		if (static::$chapter !== $oldChapter) {
			$i = 0;
			$oldChapter = static::$chapter;
		}

		$i++;

		$id = str_replace('.', '_', $key);

		return
			'<div class="row">'.
				'<div class="col-md-4">'.
					'<div>'.
						'<input type="text" size="'.$size.'" id="'.$id.'" '.
						'value="'.$value.'" '.
						'data-task="settings" data-key="'.$key.'">'.
						'<label for="'.$id.'"></label>'.
					'</div>'.
				'</div>'.
				'<div class="col-md-8"><strong>'.static::$chapter.'.'.$i.'.</strong>&nbsp;'.$caption.'</div>'.
			'</div>';
	}

	/**
	 * Loop for any entries in arr, retrieve all $property
	 * (like 'enabled') and construct the On/Off button
	 */
	private static function getBooleans(
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

				$content .= self::getRadio($prefix.$key.'.'.$property,
					$desc, $bCurrentValue);
			}
		}

		return $content;
	}

	public static function run(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get all settings
		$arrSettings = $aeSettings->getAll();

		$boxes = '';

		// Process debugging
		$box = self::getBox('Debugging', 'code-fork');
		$prefix = 'debug.';
		$content = self::getRadio($prefix.'enabled', 'Enable debug mode',
			$arrSettings['debug']['enabled']);
		$content .= self::getRadio($prefix.'development',
			'Enable full debug mode (development)',
			$arrSettings['debug']['development']);
		$boxes .= str_replace('%CONTENT%', $content, $box);

		// Process Interface
		$box = self::getBox('Interface', 'desktop');
		$prefix = 'interface.';
		$content = self::getRadio($prefix.'accent_conversion',
			'Enable this only if you\'ve problems with accentuated '.
			'characters in the treeview',
			$arrSettings['interface']['accent_conversion']);
		$content .= self::getRadio($prefix.'show_tips',
			'Show <strong>tips</strong>',
			$arrSettings['interface']['show_tree_allowed']);
		$content .= self::getRadio($prefix.'show_tree_allowed',
			'Allow the user to access to the interface',
			$arrSettings['interface']['show_tree_allowed']);
		/*$content .= self::getCombo($prefix.'skin',
			'Color of header area', $arrSettings['interface']['skin'],
			'black;black-light;blue;blue-light;green;green-light;purple;purple-ligth;red;red-light;yellow;yellow-light');*/

		/*$prefix = 'interface.footer.';
		$content .= self::getText($prefix.'left',
			'Text to show in the left side of the footer',
			$arrSettings['interface']['footer']['left']);
		$content .= self::getText($prefix.'right',
			'Text to show in the right side of the footer',
			$arrSettings['interface']['footer']['right']);*/
		$boxes .= str_replace('%CONTENT%', $content, $box);

		// Process Plugins-Buttons
		$arr = $arrSettings['plugins']['buttons'];
		$prefix = 'plugins.buttons.';
		$box = self::getBox('Plugins - Buttons', 'square');
		$content = self::getBooleans($arr, $prefix, 'enabled',
			'Enable the <strong>%s</strong> button');
		$boxes .= str_replace('%CONTENT%', $content, $box);

		// Process Plugins-Buttons-QuickIcons
		$arr = $arrSettings['plugins']['buttons'];
		$prefix = 'plugins.buttons.';
		$box = self::getBox('Plugins - Buttons - quickIcons', 'square');
		$content = self::getBooleans($arr, $prefix, 'quickIcons',
			'Show the <strong>%s</strong> button in the quickIcons area');
		$boxes .= str_replace('%CONTENT%', $content, $box);

		// Process Plugins-Content-HTML
		$arr = $arrSettings['plugins']['content']['html'];
		$prefix = 'plugins.content.html.';
		$box = self::getBox('Plugins - Content - HTML', 'square');
		$content = self::getBooleans($arr, $prefix, 'enabled',
			'Enable the <strong>%s</strong> content plugin');
		$boxes .= str_replace('%CONTENT%', $content, $box);

		// Process Plugins-Page-HTML
		$arr = $arrSettings['plugins']['page']['html'];
		$prefix = 'plugins.page.html.';
		$box = self::getBox('Plugins - Page - HTML', 'square');
		$content = self::getBooleans($arr, $prefix, 'enabled',
			'Enable the <strong>%s</strong> page HTML plugin');
		$boxes .= str_replace('%CONTENT%', $content, $box);

		// Process Plugins-Markdown
		$arr = $arrSettings['plugins']['markdown'];
		$prefix = 'plugins.markdown.';
		$box = self::getBox('Plugins - Markdown', 'square');
		$content = self::getBooleans($arr, $prefix, 'enabled',
			'Enable the <strong>%s</strong> markdown plugin');
		$boxes .= str_replace('%CONTENT%', $content, $box);

		// Process Plugins-task-markdown
		$arr = $arrSettings['plugins']['task']['markdown'];
		$prefix = 'plugins.task.markdown.';
		$box = self::getBox('Plugins - Task - Markdown', 'square');
		$content = self::getBooleans($arr, $prefix, 'enabled',
			'Enable the <strong>%s</strong> task markdown plugin');
		$boxes .= str_replace('%CONTENT%', $content, $box);

/*
		// Process Plugins-options-task-login
		$arr = $arrSettings['plugins']['options']['task']['login'];
		$prefix = 'plugins.options.task.login.';
		$box = self::getBox('Plugins - Options - Task - Login', 'log-in');
		$content = self::getText($prefix.'username', 'Login', $arr['username'], 20);
		$content .= self::getText($prefix.'password', 'Password', $arr['password'], 20);
		$boxes .= str_replace('%CONTENT%', $content, $box);
*/
		// -------------------------
		// Add form items
		$html = file_get_contents(__DIR__.'/form/show_form.html');
		$html = str_replace('%CONFIGURATION%', $boxes, $html);

		// -------------------------
		// Replace common variables

		$root = rtrim($aeFunctions->getCurrentURL(), DS);
		$html = str_replace('%ROOT%', $root, $html);

		$title = $aeSettings->getText("settings_form_title", "Global configuration");
		$html = str_replace('%TITLE%', $title, $html);

		//$html .= '<pre>'.print_r($arrSettings,true).'</pre>';

		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: text/html; charset=utf-8');
		echo $html;

		die();
	}

	/**
	 * Attach the function and responds to events
	 */
	public function bind(string $task)
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->bind('run', __CLASS__.'::run', $task);
		return true;
	}
}
