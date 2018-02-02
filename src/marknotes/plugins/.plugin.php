<?php
/*
 * Definition of a plugin - Define the global structure and features.
 * This class will be derived by plugins if f.i. /plugins/markdown
 * i.e. a specialized class for working with markdown content, or
 * /plugins/task for task plugins, ...
 */
namespace MarkNotes\Plugins;

defined('_MARKNOTES') or die('No direct access allowed');

abstract class Plugin
{
	// Contains the definition of the plugin from settings.json
	// see for instance plugins->markdown->read for the Read plugin
	protected static $arrSettings = array();

	// Contains the options for the plugin, see for instance
	// plugins->options->markdown->read in settings.json
	protected static $arrOptions = null;

	abstract public function bind(string $plugin) : bool;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// The child class should have a line like below in his
		// definition
		//	 protected static $me = __CLASS__;
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

		// The child class should have a line like below in
		// his definition
		// protected static $json_options = 'plugins.options.markdown.read';
		if (!isset(static::$json_options)) {
			throw new \Exception(get_class($this).' must have a $json_options '.
				'property and must initialize it (f.i. "plugins.options.markdown.read"). '.
				'That property indicates where in the settings.json file marknotes can '.
				'find options for the plugin. When there is no option, this property can '.
				'be empty (but should be defined).');
		}

		static::$json_settings = trim(static::$json_settings);
		static::$json_options = trim(static::$json_options);

		return self::initialize();
	}

	/**
	 * Initialize static::$arrSettings
	 */
	protected function initialize() : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		if (static::$json_settings!=='') {
			static::$arrSettings[static::$me] = $aeSettings->getPlugins(static::$json_settings);
		}

		// Be sure is initialized to the null value
		static::$arrOptions[static::$me] = null;

		return true;
	}

	/**
	 * Return a value from the plugin options. Return $default
	 * is not found or not initialized
	 *
	 * $name can contains dot like in apparence.custom-css.
	 *
	 * Consider the options for plugins->options->page->html->reveal.
	 * This is something like this :
	 *
	 * 		"reveal": {
	 *			"appearance": {
	 *				 "theme": "beige",
	 *				 "custom-css": [
	 *						"font-size=20px;"
	 *				 ]
	 *			}
	 *		}
	 *
	 * Calling getOptions for $name="appareance" will return both
	 * "theme" and "custom-css"; will return an array thus
	 * Calling getOptions for $name="appareance.theme"
	 * will just return "beige.css"; will return a string.
	 */
	protected static function getOptions(string $name, $default)
	{
		$value = $default;

		// Initialize the array, only once (the array is set to null
		// only during the initialization of the plugin)
		if (static::$arrOptions[static::$me]===null) {
			if (static::$json_options!=='') {
				$aeSettings = \MarkNotes\Settings::getInstance();

				// It's really important to store the options in
				// static::$arrOptions[static::$me] and not
				// static::$arrOptions otherwise the last loaded
				// plugins will overwrite all the previous
				// loaded options.
				static::$arrOptions[static::$me] = $aeSettings->getPlugins(static::$json_options);

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					if (static::$arrOptions[static::$me]===array()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log("There is no option found for ".
							"[".static::$json_options."]","debug");
					}
				}
				/*<!-- endbuild -->*/
			} // if (static::$json_options!=='')
		}

		if (static::$arrOptions[static::$me]!==array()) {

			$tmp = explode('.', $name);
			$arr = static::$arrOptions[static::$me];

			// Process every positions just like in "appareance.theme"
			// (as explained in the function docblock)
			for ($i=0; $i<count($tmp); $i++) {
				$node=$tmp[$i];

				if (isset($arr[$node])) {
					$arr = $arr[$tmp[$i]];
					$value = $arr;
					/*<!-- build:debug -->*/
				} else {
					$aeSettings = \MarkNotes\Settings::getInstance();
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log("Called for [".$name."] but [".$node."] is not found in settings.json. Return an empty array.", "debug");
					}
					/*<!-- endbuild -->*/
					$value = $default;
				}
			}
		}

		return $value;
	}

	/**
	 * Based on the current running task (f.i. task.export.html,
	 * task.search.search, task.listfiles.json, ...), verify if
	 * the plugin can be fired or not. This based on the plugin
	 * settings (not the options) as defined in the
	 * settings.json file.
	 * For instance, check settings.json->plugins->markdown->variables
	 *
	 *		"plugins": {
	 *			"markdown": {
	 *				"variables": {
	 *		 			"not_if_task": [ "task.search.search" ],
	 *					"only_if_task": []
	 *			 	}
	 *			}
	 *		}
	 */
	protected static function canRun() : bool
	{
		$aeSession = \MarkNotes\Session::getInstance();
		$task = $aeSession->get('task', '');

		$bCanRun = true;

		if ($task!=='') {
			// If "only_if_task" is mentionned (it's an array);
			// verify that the current running task is mentionned
			// in "only_if_task". If so, yes,
			// the plugin can be fired
			$arr = static::$arrSettings[static::$me]['only_if_task'] ?? array();
			if ($arr !== array()) {
				$bCanRun = in_array($task, $arr);
			}

			// And the same for "not_if_task" but the opposite :
			// if the running task is mentionned in "not_if_task"
			// then the plugin can't be fired.
			if ($bCanRun) {
				$arr = static::$arrSettings[static::$me]['not_if_task'] ?? array();
				if ($arr !== array()) {
					$bCanRun = !(in_array($task, $arr));
				}
			}
		} // if ($task!=='')

		return $bCanRun;
	}

}
