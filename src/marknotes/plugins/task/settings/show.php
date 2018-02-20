<?php
/**
 * Show the settings's form
 *
 * Answer to URL like index.php?task=task.settings.show
 */
namespace MarkNotes\Plugins\Task\Settings;

defined('_MARKNOTES') or die('No direct access allowed');

class Show extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.settings';
	protected static $json_options = 'plugins.options.task.settings';

	/**
	 * Make the table of content with links to each "chapter"
	 */
	private static function makeTOC(array $arr) : string
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$chapter = 0;
		$toc = '';

		foreach ($arr as $title => $value) {
			$id = $aeFunctions->slugify($title);
			$chapter++;
			$toc .= '<li class="toc3">'.
				'<a href="#'.$id.'"><span style="min-width:30px;display:inline-block;">'.$chapter.'.</span> '.$value['icon'].' '.$title.'</a>'.
				'</li>';
		}

		$toc = '<details><summary>'.$aeSettings->getText('settings_show_categories').'</summary><nav role="navigation" id="toc"><ul>'.$toc.'</ul></nav></details>';

		return $toc;
	}

	// Process Plugins-Content-HTML
	/*private static function getContentHTML(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Content - HTML', 'square');
		$content = self::loopBooleans($arr, $key, 'enabled',
			'Enable the <strong>%s</strong> content plugin');
		return str_replace('%CONTENT%', $content, $box);
	}*/

	// Process Plugins-Page-HTML
	/*private static function getPageHTML(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Page - HTML', 'square');
		$content = self::loopBooleans($arr, $key, 'enabled',
			'Enable the <strong>%s</strong> page HTML plugin');
		return str_replace('%CONTENT%', $content, $box);
	}*/

	// Process Plugins-Markdown
	/*private static function getMarkdown(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Markdown', 'square');
		$content = self::loopBooleans($arr, $key, 'enabled',
			'Enable the <strong>%s</strong> markdown plugin');
		return str_replace('%CONTENT%', $content, $box);
	}*/

	// Process Plugins-Task-Markdown
	/*private static function getTaskMarkdown(array $arr, string $key) : string
	{
		$key = $key.'.';
		$box = self::getBox('Plugins - Task - Markdown', 'square');
		$content = self::loopBooleans($arr, $key, 'enabled',
			'Enable the <strong>%s</strong> task markdown plugin');
		return str_replace('%CONTENT%', $content, $box);
	}*/

	/**
	 * Built the HTML for the form
	 */
	private static function doIt(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// For simplicity, every plugin will have a class in
		// the entries/ folder and will implement a getFormItem()
		// function
		$path = rtrim(__DIR__, DS).DS.'entries'.DS;

		require_once($path.'.plugin.php');

		$aeEntry = new \MarkNotes\Plugins\Task\Settings\Entries\Plugin;
		$aeEntry->init($aeSettings->getAll());

		$arr = $aeFiles->rglob('*.php', $path);

		$arrSettingsForm = array();

		foreach ($arr as $file) {
			// Don't process the "parent" plugin
			if (basename($file) !== '.plugin.php') {

				require_once($file);

				// Dynamically derive the name of the class
				$className = basename($file);
				$className = $aeFiles->removeExtension($className);
				$className = '\\MarkNotes\\Plugins\\Task\\'.
					'Settings\\Entries\\MN_'.$className;

				// Load the class and retrieve the form item
				$class = new $className;

				$html = $class->getFormItem();
				list($title, $icon) = $class->getTitle();

				$arrSettingsForm[$title] = array('icon'=>$icon, 'html'=>$html);

				unset($class);

			}
		}

		// Sort the array. The key is the title of the option so
		// sort ascending on the title
		ksort($arrSettingsForm);

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// -------------------------
		// We've now all of our configuration elements (checkboxes,
		// text boxes, ...). Add them in the template
		$html = $aeFiles->getContent(__DIR__.'/form/show_form.html');

		$title = $aeSettings->getText("settings_form_title", "Global configuration");
		$html = str_replace('%TITLE%', '<h1>'.$title.'</h1>', $html);

		// Add a table of content
		$toc = self::makeTOC($arrSettingsForm);
		$html = str_replace('%TOC%', $toc, $html);

		// Build the form
		$sForm = '';

		$chapter = 0;

		$nbrOptions = 0;

		foreach ($arrSettingsForm as $title => $value) {

			$id = $aeFunctions->slugify($title);

			$chapter++;

			$settingsForm = $value['html'];

			$settingsForm = str_replace('%ID%', $id, $settingsForm);

			$pattern = '/%COUNTER%/i';

			if (preg_match_all($pattern, $settingsForm, $matches, PREG_OFFSET_CAPTURE)) {

				$j = count($matches[0]);

				for ($i=$j; $i>0; $i--) {

					if ($i>1) {
						$nbrOptions++;
					}

					$count = $chapter. ($i>1 ? '.'.($i-1) : '');

					list ($pattern, $pos) = $matches[0][$i-1];

					$left = substr($settingsForm, 0, $pos);
					$right = substr($settingsForm, $pos + strlen($pattern));

					$settingsForm = $left.$count.$right;

				}
			}

			$sForm .= $settingsForm;
		}

		$html = str_replace('%CONFIGURATION%', $sForm, $html);

		// -------------------------
		// Replace common variables
		$root = rtrim($aeFunctions->getCurrentURL(), DS).DS;
		$html = str_replace('%ROOT%', $root, $html);

		$tmp = $aeSettings->getText('settings_nbr_of_options');
		$tmp = str_replace('%s', $nbrOptions, $tmp);
		$html = str_replace('%NBROPTIONS%', $tmp, $html);

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

}
