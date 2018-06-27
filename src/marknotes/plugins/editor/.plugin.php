<?php
/*
 * Definition of a button plugin - Define the global structure
 * and features. This class will be derived by plugins if f.i.
 * /plugins/buttons/page/content/docx.php
 */
namespace MarkNotes\Plugins\Button;

defined('_MARKNOTES') or die('No direct access allowed');

require_once (dirname(__DIR__).DS.'.plugin.php');

abstract class Plugin extends \MarkNotes\Plugins\Plugin
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// The child class should have a line like below in his definition
		//	 protected static $me = __CLASS__;
		if (!isset(static::$me)) {
			throw new \Exception(get_class($this).' must have a $me '.
				'property and must initialize it exactly like this : "protected static $me = __CLASS__"');
		}

		// The child class should have a line like below in his definition
		//	 protected static $json_settings = 'plugins.buttons.page.content.edito';
		if (!isset(static::$json_settings)) {
			throw new \Exception(get_class($this).' must have a $json_settings '.
				'property and must initialize it (f.i. '. '"plugins.buttons.page.content.editor"). '.
				'That property indicates where in the settings.json '.
				'file marknotes can find the settings '.
				'(enabled, not_if_task, only_if_task, ...) for that plugin');
		}

		return true;
	}

	/**
	 * The editor can be enabled ONLY if the associated page HTML
	 * plugin is enabled. This because the button will call a javascript
	 * function that is implemented in a .js file; loaded by a page HTML
	 * plugin (the "linked plugin")
	 */
	private static function linkedPlugin() : bool
	{
		$bReturn = true;

		if (trim(static::$json_linked)!=='') {

			// Check that the linked plugin is also enabled

			$aeSettings = \MarkNotes\Settings::getInstance();
			$arrSettings = $aeSettings->getPlugins(static::$json_linked);

			$bReturn = boolval($arrSettings['enabled']?? 0);

			if (!$bReturn) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();

					$str = "The plugin is enabled [".
						static::$json_settings."] but requires that the page ".
						"plugin [".static::$json_linked."] is enabled too and ".
						"it's not the case. Both should be enabled or ".
						"disabled in the same time...";

					$aeDebug->log($str, "warning");

				}
				/*<!-- endbuild -->*/
			}
		}

		return $bReturn;
	}

	/**
	 * Read the .js and .css files for the editor plugin
	 * @param array $buttons [description]
	 */
	public static function add(&$buttons = array()) : bool
	{
		// $me is something like
		// MarkNotes\Plugins\Buttons\TOCButton
		// extract the last part : TOCButton and then
		// remove "Button"
		$fname = strtolower(basename(static::$me));
		$fname = str_replace('button', '', $fname);

		// Try to find the .js script.
		// If the class is MarkNotes\Plugins\Buttons\TOC,
		// suppose to find the code in the "toc/toc.js" file
		$fname = __DIR__.DS.$fname.DS.$fname.'.';

		$arr=array('js','css');

		foreach($arr as $ext) {
			if (is_file($fname.$ext)) {
				$content = file_get_contents($fname.$ext);

				// Define the position of the button
				$position = self::getOptions('position', 1);

				$content = str_replace('$POSITION$', $position, $content);

				// Do we need to add a divider after the button ?
				$divider = '';
				if (($where = trim(self::getOptions('add_divider', '')))!=='') {
					$position =  $position + (strtolower($where)=='after'?1:-1);
					$divider = 'toolbar.insertItem('.$position.', {"type": "divider"});';
				}

				$content = str_replace('$DIVIDER$', $divider, $content);

				$buttons[$ext][static::$me] = $content;
			}
		}

		return true;
	}

	/**
	 * Determine if the plugin can add his button
	 */
	protected static function canAdd() : bool
	{
		$bReturn = true;

		// If this editor plugin button is linked to a plugin,
		// check that that plugin is enabled.
		// For instance, if the encrypt plugin isn't enabled,
		// the encrypt button shouldn't appears in the editor
		if (isset(static::$json_linked)) {
			$bReturn = self::linkedPlugin();

		}
		return $bReturn;
	}

	/**
	 * Capture the add.button event and attach the add() function
	 */
	public function bind(string $plugin) : bool
	{
		if ($this->canAdd()) {
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->bind('add.buttons', static::$me.'::add', $plugin);
		}
		return true;
	}
}
