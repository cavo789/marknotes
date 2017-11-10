<?php

/**
 * Autoloader for Makrnotes class_exists
 *
 * This file should be saved in the /marknotes/includes folder
 */

namespace MarkNotes;

//defined('_MARKNOTES') or die('No direct access allowed');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

class Autoload
{
	public static function register()
	{
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	public static function autoload($class)
	{

		// This file should be saved in the /marknotes/includes folder so
		// the root folder for Marknotes's classes is three folders up
		$root = rtrim(dirname(dirname(dirname(__FILE__))), DS).DS;

		// Only for MarkNotes classes and not third parties libraries f.i.
		if (substr($class, 0, 10) === 'MarkNotes\\') {
			$parts = preg_split('#\\\#', $class);

			$className = array_pop($parts);

			$path = implode(DS, $parts);
			$file = $className.'.php';

			$filepath = $root.strtolower($path.DS.$file);

			if (!file_exists($filepath)) {
				echo '<strong>autoload - The file '.$filepath.' is missing!</strong>';

				/*<!-- build:debug -->*/
				if (class_exists("\MarkNotes\Debug")) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					if ($aeDebug->enable()) {
						echo '<pre>'.print_r(debug_backtrace(3), true).'</pre>';
					}
				}
				/*<!-- endbuild -->*/

				return false;
			}

			require $filepath;
		}
	}
}
