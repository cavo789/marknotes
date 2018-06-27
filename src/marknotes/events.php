<?php

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Events
{
	protected static $hInstance = null;

	private static $arrEvents = array();

	public function __construct()
	{
		self::$arrEvents = array();
		return true;
	}

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Events();
		}
		return self::$hInstance;
	}

	/**
	 * Remove any bindings functions, reset events
	 */
	public static function reset()
	{
		self::$arrEvents = array();
		return true;
	}

	public static function getEvents()
	{
		return self::$arrEvents;
	}

	/**
	 * Call an event and fires every attached functions if there are
	 * somes
	 *
	 * $bStopOnFirstTrue : when a plugin is called (f.i. export.pdf),
	 * when the file has been created, no need to call a second or a
	 * third export plugin when the job was already done. So, when
	 * $bStopOnFirstTrue is set on True, this function will stop
	 * to call plugins as soon as one plugin has returned True (which
	 * means "I've done the job")
	 */
	public static function trigger(string $event = '', array &$args = array(), bool $bStopOnFirstTrue = false) : bool
	{
		$plugin = 'main';

		$bReturn = true;

		// We can fire "run" event and then every plugins who've
		// implemented the "run" event will be fired or, better,
		// we can fire "task.acls.load::run" to fire only that one.
		//
		// The syntax is therefore :
		//		First part is the $type (as used in LoadPlugins) :
		// f.i task.acls.load
		//	  Use "::" as separator
		//		Then the event f.i. run

		if (strpos($event, '::') !== false) {
			$plugin = substr($event, 0, strpos($event, '::'));
			$event = substr($event, strpos($event, '::') + 2);
		}

		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		$aeDebug = \MarkNotes\Debug::getInstance();
		/*<!-- endbuild -->*/

		/*<!-- build:debug -->*/
		//if ($aeDebug->getDevMode()) {
		//	$aeDebug->here("Trigger ".$plugin.'::'.$event, 1);
		//	$aeDebug->log("Trigger ".$plugin.'::'.$event, "debug");
		//}
		/*<!-- endbuild -->*/

		// $event is, for instance, export.txt
		// Check if there are attached events for this specific event
		// but not only.  Check if there are events attached for
		// 'export.*' i.e. whatever the format.
		//
		// In a global way, if there is a dot (like in export.txt),
		// extract the first item (export) and add a wildcard
		// (export.*)

		$arr=array($event);
		if (strpos($event, '.')) {
			$tmp=substr($event, 0, strpos($event, '.'));
			array_push($arr, $tmp.'.*');
		}

		foreach ($arr as $event) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log("Trigger started for [".$plugin."][".$event."]", "debug", 2);
				/*if ($aeDebug->getDevMode()) {
					echo("<pre style='background-color:yellow;'>".
						__FILE__." - ".__LINE__." ".
						"<h4>List of binded functions for [".$plugin."][".$event."]:</h4>".
						print_r(self::$arrEvents, true).
						"</pre>");
				}*/
			}
			/*<!-- endbuild -->*/

			if (isset(self::$arrEvents[$plugin][$event])) {
				if (count(self::$arrEvents[$plugin][$event]) > 0) {
					foreach (self::$arrEvents[$plugin][$event] as $func) {
						if (is_callable($func)) {
							/*<!-- build:debug -->*/
							if ($aeSettings->getDebugMode()) {
								$aeDebug->log('	call ['.$func.']', 'debug');
							}
							/*<!-- endbuild -->*/

							// Get the value returned by the function in $bReturn
							$bReturn = call_user_func_array($func, $args);
							if ($bReturn && $bStopOnFirstTrue) {
								/*<!-- build:debug -->*/
								if ($aeSettings->getDebugMode()) {
									$aeDebug->log('	'.$func.' has done the job, stop', 'debug');
								}
								/*<!-- endbuild -->*/

								break;
							}
						} else {
							// OUCH ! The function isn't callable
							/*<!-- build:debug -->*/
							if ($aeDebug->getDevMode()) {
								$aeDebug->here('Event '.$event.', '.$func.' is not '.
								'callable [plugin '.$plugin.']', 3);
							}
							/*<!-- endbuild -->*/
						}
					}
				}
			/*<!-- build:debug -->*/
			} else {
				// No listener => there is nothing to return.
				$bReturn = false;

				if ($aeSettings->getDebugMode()) {
					if (!isset(self::$arrEvents[$plugin][$event])) {
						// Don't output log info if the $event finish by ".*"
						if (substr($event, -2) !== '.*') {
							$aeDebug->log('There is no listener for '.
								'the event ['.$plugin.'] ['.$event.']', 'notice');
							//$aeDebug->here('There is no listener for '.
							//	'the event ['.$plugin.'] ['.$event.']', 3);
						}
					}
				}
			/*<!-- endbuild -->*/
			}
		}

		return $bReturn;
	}

	/**
	 * Add a function (a "callable" one) into the list of listeners for a specific event,
	 * like 'task.export.html' or 'render.js'.
	 *
	 * Use !in_array to be sure that the same function is there only once
	 */
	public static function bind(string $event, string $func, string $plugin)
	{
		/*<!-- build:debug -->*/
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		//if ($aeDebug->getDevMode()) {
		//	$aeDebug->here("Binding event [".$plugin."][".$event."] in [".$func."]", 1);
		//	$aeDebug->log("Binding event [".$plugin."][".$event."] in [".$func."]", "debug");
		//}
		/*<!-- endbuild -->*/

		if (!isset(self::$arrEvents[$plugin])) {
			self::$arrEvents[$plugin]=array();
		}
		if (isset(self::$arrEvents[$plugin][$event])) {
			if (!in_array($func, self::$arrEvents[$plugin][$event])) {
				self::$arrEvents[$plugin][$event][] = $func;
			}
		} else {
			// This event isn't yet known
			self::$arrEvents[$plugin][$event][] = $func;
		}

		return true;
	}

	/**
	 * Extract the name of the class from the .php file
	 */
	private static function getNameSpaceAndClassName($file)
	{
		$sReturn = null;

		$aeFiles = \MarkNotes\Files::getInstance();
		$content = $aeFiles->getContent($file);

		if (preg_match('/^namespace (.*);/m', $content, $matches)) {
			$sReturn = '\\'.trim($matches[1]);
		}

		if (preg_match('/^class ([^ \n]*).*$/m', $content, $matches)) {
			$sReturn .= '\\'. trim($matches[1]);
		}

		return $sReturn;
	}

	/**
	* $type = 'markdown'  			==> load every 'markdown' plugins
	* $type = 'markdown.variables'  ==> load only the 'variables' plugin; not all
	*/
	public static function loadPlugins(string $type = 'content')
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if ($type !== '') {
			// The plugins folder is under /marknotes
			// Note : $type can contains dot like 'task.export',
			// in that case it means folder task subfolder
			// export so replace dot by /
			$dir = rtrim(dirname(__DIR__), DS).'/marknotes/plugins/'.str_replace('.', DS, $type).DS;

			// $dir is something like
			// c:/site/marknotes/plugins/task/export/before/
			// i.e. a folder where we can find plugins.
			$plugins=array();

			if ($aeFolders->exists($dir)) {
				// Get the list of plugins in that folder but,
				// really, the ones that are specified in the
				// settings.json file, so we'll only take the
				// enabled ones.
				$plugins = $aeSettings->getPlugins($type);

				if ($plugins===array()) {
					// No entry in the settings.json file but the
					// directory exists on disk => get the list of
					// .php files from that folder
					$file=basename($dir);
					$tmpdir=dirname($dir).DS;
					if ($aeFiles->exists($fname = $tmpdir.$file.DS.$file.'.php')) {
						$dir=$tmpdir.$file.DS;
						$plugins=array($file=>array('enabled'=>1));
					}
				}
			}

			if ($plugins == array()) {
				// But, perhaps, it isn't a folder but a file like
				// in c:/site/marknotes/plugins/task/export/txt/
				// here, txt is a plugin (txt.php), not a folder.

				$file=basename($dir);
				$dir=dirname($dir).DS;

				if ($aeFiles->exists($fname = $dir.$file.'.php')) {
					$plugins=array($file=>array('enabled'=>1));
				} else {
					// No... so, in the case of f.i.
					// task.optimize.clear, optimize/clear.php
					// was not found, detect if optimize.php
					// exists and if so, probably that script
					// will implement a functionnality for
					// the event optimize.Clear

					if ($aeFiles->exists($fname = $dir.basename($dir).'.php')) {
						$plugins=array(basename($dir)=>array('enabled'=>1));
					}
				}
			}

			// And if the plugin exists on the filesystem, load it
			if (count($plugins) > 0) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("Loading plugins for [".$type."]", "debug", 3);
				}
				/*<!-- endbuild -->*/

				// Plugins extends a parent class stored in a file
				// called .plugin and stored in the same folder so if
				// that file is there, load it first.
				if ($aeFiles->exists($file = $dir.'.plugin.php')) {
					require_once($file);
				} else {
					// For task plugins, the .plugin.php file isn't
					// stored in the same folder than the plugin
					// (.i.e. not in /plugins/task/listfiles/get.php)
					// but in the parent folder (in /plugins/task/)
					if ($aeFiles->exists($file = dirname($dir).'/.plugin.php')) {
						require_once($file);
					}
				}

				// plugins is an array like this :
				//		"anchor": {
				//			"enabled": 1,
				//			"not_if_task": [],
				//			"only_if_task": []
				//		}

				// The name of the plugin (anchor here) and an
				// array with enabled = tell if the plugin is
				// active (so should be loaded) or not not_if_task
				// and only_if_task to restrict his use to
				// speficied tasks

				foreach ($plugins as $name => $plgSettings) {
					// Only if enabled
					if (($plgSettings['enabled']??0) == 1) {
						// Get the file name (f.i. anchor.php)

						if (substr($name, -4) !== '.php') {
							$name .= '.php';
						}

						if ($aeFiles->exists($file = $dir.$name)) {
							// Load the plugin
							require_once($file);

							// And retrieve its namespace and class
							// name f.i.
							// "\MarkNotes\Plugins\Content\HTML\ReplaceVariables"
							$class = self::getNameSpaceAndClassName($file);

							// Instanciate the class (plugin)
							$plug = new $class;

							// and run the bind() function
							// return true when the plugin has bind a
							// function return false f.i. when the
							// plugin is for HTML output and the
							// task is pdf
							$return = $plug->bind($type);

							/*<!-- build:debug -->*/
							if ($return) {
								$aeSettings = \MarkNotes\Settings::getInstance();
								$aeDebug = \MarkNotes\Debug::getInstance();
								if ($aeDebug->getDevMode()) {
									$aeDebug->log('	Load plugin '.$file);
								}
							}
							/*<!-- endbuild -->*/
						}
					/*<!-- build:debug -->*/
					} else { // if (($plgSettings['enabled']??0)
						if ($aeSettings->getDebugMode()) {
							$aeDebug = \MarkNotes\Debug::getInstance();
							$aeDebug->log("Plugin ".$dir.$name." ".
								"has been disabled in ".
								"settings.json", "debug");
						}
					/*<!-- endbuild -->*/
					} // if (($plgSettings['enabled']??0)
				} // foreach ($plugin as $name => $enabled)
			} // if(count($plugins)>0)
		}

		return true;
	}
}
