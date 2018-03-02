<?php
/**
 * Update settings.json
 *
 * Answer to URL like index.php?task=task.settings.update&key=interface.show_tip&value=0
 */
namespace MarkNotes\Plugins\Task\Settings;

defined('_MARKNOTES') or die('No direct access allowed');

class Update
{
	private static function buildArray(string $key, string $value,
		array $arr) : array
	{
		// Just in case of, remove the ending dot if present
		$key=rtrim($key, '.');
		$tmp = explode('.', $key);
		$node = array_shift($tmp);
		if (count($tmp)>0) {
			$arr[$node] = self::buildArray(implode('.', $tmp), $value, $arr);
		} else {
			$arr[$node] = $value;
		}

		return $arr;
	}

	/**
	 * Recursive ksort() function.
	 * Allow to sort an array based on keys, recursively
	 * @link https://gist.github.com/cdzombak/601849#file-ksortrecursive-php
	 */
	private static function ksortRecursive(&$array, $sort_flags = SORT_REGULAR) {
		if (!is_array($array)) return false;
		ksort($array, $sort_flags);
		foreach ($array as &$arr) {
			self::ksortRecursive($arr, $sort_flags);
		}
		return true;
	}

	private static function doIt(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$key = trim($aeFunctions->getParam('key', 'string', '', false));
		$value = $aeFunctions->getParam('value', 'string', '', false);

		// Don't use the escape form
		$value = str_replace('&#39;', "'", $value);
		$value = str_replace('&#34;', '"', $value);

		$key = $aeFiles->sanitize($key);

		$arrNew = array();
		$arrNew = self::buildArray($key, $value, $arrNew);

		$rootFolder = $aeSettings->getFolderWebRoot();

		$arrSettings = array();

		// If there is already a settings.json file, get its content
		if ($aeFiles->exists($json = $rootFolder.'settings.json')) {
			$arrSettings = json_decode($aeFiles->getContent($json), true);
		}
		// And merge it with the new settings
		if (count($arrSettings)>0) {
			$arrSettings = array_replace_recursive($arrSettings, $arrNew);
		} else {
			$arrSettings = $arrNew;
		}

		// Sort the array by key, recursively
		self::ksortRecursive($arrSettings);

		// Write the file
		$aeFiles->rewrite($json, json_encode($arrSettings, JSON_PRETTY_PRINT));

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = boolval($arrSettings['enabled'] ?? false);

		if ($bCache) {
			// Refresh the cache for what concerns the interface
			$aeCache = \MarkNotes\Cache::getInstance();
			// Clear the cache for this note : clear every cached
			// items with a tag equal to $fullname i.e. the fullname
			// of the note
			$aeCache->deleteItemsByTag(md5('interface'));
		}

		return true;
	}

	public static function run(&$params = null)
	{
		// The update requires to be authenticated
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if (boolval($aeSession->get('authenticated', 0))) {

			self::doIt($params);

			header('Content-Type: application/json');
			echo json_encode(
				array(
					'status'=>1,
					'message'=>$aeSettings->getText('settings_saved')
					)
				);
		} else {
			// The user isn't logged in, he can't modify settings

			header('Content-Type: application/json');
			echo json_encode(
				array(
					'status'=>0,
					'message'=>$aeSettings->getText('not_authenticated')
					)
				);
		} // if (boolval($aeSession->get('authenticated', 0)))

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
