<?php
/**
 * Show what is in the cache
 *
 * Answer to index.php?task=task.search.debug
 */
namespace MarkNotes\Plugins\Task\Search;

defined('_MARKNOTES') or die('No direct access allowed');

class Debug extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.search';
	protected static $json_options = 'plugins.options.task.search';

	/**
	 * Get the content of the cache
	 * @return string
	 */
	private static function getFromCache()
	{
		$return = null;

		$aeDebug = \MarkNotes\Debug::getInstance();


		return $arr;
	}

 	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arr = array();

		/*<!-- build:debug -->*/

		if ($aeSettings->getDebugMode() &&  $aeDebug->getDevMode()) {
			$aeCache = \MarkNotes\Cache::getInstance();
			$arr = $aeCache->debug_getItemsByTag('search');
		}
		/*<!-- endbuild -->*/

		header('Content-Type: application/json');
		echo json_encode($arr, JSON_PRETTY_PRINT);

		return true;
	}
}
