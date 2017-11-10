<?php
/**
 * Optimize page headers, add f.i. the cache-control HTTP header
 */

 namespace MarkNotes\Plugins\Page\HTML\Optimize;

 defined('_MARKNOTES') or die('No direct access allowed');

class Headers
{

	public static function run(string $str, array $arrOptimize) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$useBrowserCache = boolval($arrOptimize['browser_cache']??false);

		if ($useBrowserCache) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('Cache - 30 days', 'debug');
			}
			/*<!-- endbuild -->*/

			// 30 days (60sec * 60min * 24hours * 30days)
			header("Cache-Control: max-age=2592000");
		} else { // if($useBrowserCache)
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('Cache - No, disable caching', 'debug');
			}
			/*<!-- endbuild -->*/

			// Set headers to NOT cache a page
			header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
			header("Pragma: no-cache"); //HTTP 1.0
			header("Expires: Sat, 31 Dec 2016 01:00:00 GMT"); // Date in the past
		} // if($useBrowserCache)

		return true;
	}
}
