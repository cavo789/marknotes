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
	private static $option = 0;
	private static $arrHeadings = array();

	private static function getBox(string $title, string $icon='') : string
	{
		static::$chapter++;
		// Reset the option numbering each time the chapter change
		static::$option = 0;

		if ($icon!=='') {
			$icon = '<i class="fa fa-'.$icon.'" '.
				'aria-hidden="true"></i>&nbsp;';
		}

		self::$arrHeadings[static::$chapter] =
			$icon.static::$chapter.'. '.$title;

		return
			'<div class="box box-success">'.
				'<div class="box-header">'.
					'<h3 id="'.static::$chapter.'" class="box-title">'.
					$icon.static::$chapter.'. '.$title.'</h3>'.
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
		static::$option+=1;

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
				'<div class="col-md-10"><strong>'.static::$chapter.'.'.
					static::$option.'.</strong>&nbsp;'.
					$caption.' '.$tip.'</div>'.
			'</div>';
	}

	// Checkbox (radio button)
	private static function getRadio(string $key, string $caption,
		string $value) : string
	{
		static::$option+=1;

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
				'<div class="col-md-10"><strong>'.
					static::$chapter.'.'.static::$option.
					'</strong>&nbsp;'.
					$caption.' '.$tip.'</div>'.
			'</div>';
	}

	// Text
	private static function getText(string $key, string $caption,
		string $value) : string
	{
		static::$option+=1;

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
				'<div class="col-md-10"><strong>'.static::$chapter.'.'.
					static::$option.'.</strong>&nbsp;'.
					$caption.' '.$tip.'</div>'.
			'</div>';
	}

	/**
	 * Loop for any entries in arr, retrieve all $property
	 * (like 'enabled') and construct the On/Off button
	 */
	private static function loopBooleans(
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

	/**
	 * Make the table of content with links to each "chapter"
	 */
	private static function makeTOC() : string
	{
		$toc = '';

		foreach (static::$arrHeadings as $id=>$entry) {
			$toc .= '<li class="toc3">'.
				'<a href="#'.$id.'">'.$entry.'</a>'.
				'</li>';
		}

		$toc = '<nav role="navigation" id="toc"><ul>'.$toc.'</ul></nav>';

		return $toc;
	}

	// Process debugging
	private static function getDebugging(array $arr, string $key) : string
	{
		$box = self::getBox('Debugging', 'code-fork');
		$key = $key.'.';
		$content = self::getRadio($key.'enabled', 'Enable debug mode',
			$arr['enabled']);
		$content .= self::getRadio($key.'development',
			'Enable full debug mode (development)',
			$arr['development']);
		return str_replace('%CONTENT%', $content, $box);
	}

	// Process regional settings
	private static function getRegional(array $arr, string $key) : string
	{
		$box = self::getBox('Regional', 'globe');
		$key = $key.'.';
		$content = self::getCombo($key.'language', 'Language',
			$arr['language'], 'fr;en');
		$content .= self::getText($key.'locale', 'Locale',
			$arr['locale']);
		$content .= self::getText($key.'timezone', 'Timezone',
			$arr['timezone']);
		return str_replace('%CONTENT%', $content, $box);
	}

	// Process Interface
	private static function getInterface(array $arr, string $key) : string
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Retrieve the root folder
		$root = $aeSettings->getFolderWebRoot();

		$box = self::getBox('Interface', 'desktop');
		$key = $key.'.';
		$content = self::getRadio($key.'accent_conversion',
			'Enable this only if you\'ve problems with accentuated '.
			'characters in the treeview',
			$arr['accent_conversion']);
		$content .= self::getRadio($key.'show_tips',
			'Show <strong>tips</strong>',
			$arr['can_see']);
		$content .= self::getRadio($key.'can_see',
			'Allow the user to access to the interface',
			$arr['can_see']);
		$content .= self::getCombo($key.'skin',
				'Color of header area', $arr['skin'],
				'black;black-light;blue;blue-light;green;green-light;purple;purple-ligth;red;red-light;yellow;yellow-light');
		$content .= self::getText($key.'logo', 'Logo '.
			'(<em>The image should be saved in the <strong>'.
			str_replace('/', DS, $root.'assets/images/').'</strong> '.
			'folder</em>)', $arr['logo']);
		return str_replace('%CONTENT%', $content, $box);
	}

	// Folder
	private static function getFolder(array $arr, string $key) : string
	{
		$box = self::getBox('Folder', 'folder');
		$content = self::getText('folder', 'Folder', $arr['folder']);
		return str_replace('%CONTENT%', $content, $box);
	}

	// Process Plugins-Buttons-Enabled Y/N
	private static function getButtons(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Buttons', 'square');
		$content = self::loopBooleans($arr, $key, 'enabled',
			'Enable the <strong>%s</strong> button');
		return str_replace('%CONTENT%', $content, $box);
	}

	// Process Plugins-Buttons-QuickIcons Y/N
	private static function getButtonsQuickIcons(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Buttons - quickIcons', 'square');
		$content = self::loopBooleans($arr, $key, 'quickIcons',
			'Show the <strong>%s</strong> button in the quickIcons area');
		return str_replace('%CONTENT%', $content, $box);
	}

	// Process Plugins-Content-HTML
	private static function getContentHTML(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Content - HTML', 'square');
		$content = self::loopBooleans($arr, $key, 'enabled',
			'Enable the <strong>%s</strong> content plugin');
		return str_replace('%CONTENT%', $content, $box);
	}

	// Process Plugins-Page-HTML
	private static function getPageHTML(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Page - HTML', 'square');
		$content = self::loopBooleans($arr, $key, 'enabled',
			'Enable the <strong>%s</strong> page HTML plugin');
		return str_replace('%CONTENT%', $content, $box);
	}

	// Process Plugins-Markdown
	private static function getMarkdown(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Markdown', 'square');
		$content = self::loopBooleans($arr, $key, 'enabled',
			'Enable the <strong>%s</strong> markdown plugin');
		return str_replace('%CONTENT%', $content, $box);
	}

	// Process Plugins-Task-Markdown
	private static function getTaskMarkdown(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Task - Markdown', 'square');
		$content = self::loopBooleans($arr, $key, 'enabled',
			'Enable the <strong>%s</strong> task markdown plugin');
		return str_replace('%CONTENT%', $content, $box);
	}

	// Process Plugins-Options-Task-Login
	private static function getOptionsTaskLogin(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Options - Task - Login', 'sign-in');
		$content = self::getText($key.'username', 'Login',
			$arr['username']);
		$content .= self::getText($key.'password', 'Password',
			$arr['password']);
		return str_replace('%CONTENT%', $content, $box);
	}

	/**
	 * Built the HTML for the form
	 */
	private static function doIt(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get all settings
		$arr = $aeSettings->getAll();

		// -------------------------
		// And generate each part ("chapters")
		$boxes = '';
		$boxes .= self::getDebugging($arr['debug'], 'debug');
		$boxes .= self::getRegional($arr['regional'], 'regional');
		$boxes .= self::getInterface($arr['interface'], 'interface');
		$boxes .= self::getFolder($arr, 'folder');
		$boxes .= self::getButtons($arr['plugins']['buttons'],
			'plugins.buttons');
		$boxes .= self::getButtonsQuickIcons($arr['plugins']['buttons'],
			'plugins.buttons');
		$boxes .= self::getContentHTML($arr['plugins']['content']['html'],
			'plugins.content.html');
		$boxes .= self::getPageHTML($arr['plugins']['page']['html'],
			'plugins.page.html');
		$boxes .= self::getMarkdown($arr['plugins']['markdown'],
			'plugins.markdown');
		$boxes .= self::getTaskMarkdown($arr['plugins']['task']['markdown'],
			'plugins.task.markdown');
		$boxes .= self::getOptionsTaskLogin($arr['plugins']['options']['task']['login'], 'plugins.options.task.login');

		// -------------------------
		// Done, we've our form with all elements (checkboxes,
		// text boxes, ...). Add them in the template
		$html = $aeFiles->getContent(__DIR__.'/form/show_form.html');
		$html = str_replace('%CONFIGURATION%', $boxes, $html);

		// -------------------------
		// Replace common variables
		$root = rtrim($aeFunctions->getCurrentURL(), DS).DS;
		$html = str_replace('%ROOT%', $root, $html);

		$title = $aeSettings->getText("settings_form_title", "Global configuration");
		$html = str_replace('%TITLE%', '<h1>'.$title.'</h1>', $html);

		// Add a table of content
		$toc = self::makeTOC();
		$html = str_replace('%TOC%', $toc, $html);

		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: text/html; charset=utf-8');
		echo $html;

		die();
	}

	public static function run(&$params = null) : bool
	{
		$aeSession = \MarkNotes\Session::getInstance();

		$bReturn = false;

		if (boolval($aeSession->get('authenticated', 0))) {
			$bReturn = self::doIt($params);
		} else {
			// The user isn't logged in, he can't modify settings
			$aeSettings = \MarkNotes\Settings::getInstance();
			echo '<p class="text-danger">'.
				$aeSettings->getText('not_authenticated').'</p>';
		}

		return $bReturn;
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
