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
			$arr[$node] = self::buildArray(implode('.',$tmp), $value, $arr);
		} else {
			$arr[$node] = $value;
		}

		return $arr;
	}

	public static function run(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$key = trim($aeFunctions->getParam('key', 'string', '', false));
		$value = trim($aeFunctions->getParam('value', 'string', '', false));

		$key = $aeFiles->sanitizeFileName($key);

		$arrNew = array();
		$arrNew = self::buildArray($key, $value, $arrNew);

		$rootFolder = $aeSettings->getFolderWebRoot();

		$arrSettings = array();

		// If there is already a settings.json file, get its content
		if (is_file($json = $rootFolder.'settings.json')) {
			$arrSettings = json_decode(file_get_contents($json), true);
		}

		// And merge it with the new settings
		if (count($arrSettings)>0) {
			$arrSettings = array_replace_recursive($arrSettings, $arrNew);
		} else {
			$arrSettings = $arrNew;
		}

		// Write the file
		$fp = fopen($json, 'w');
		fwrite($fp, json_encode($arrSettings, JSON_PRETTY_PRINT));
		fclose($fp);

		header('Content-Type: application/json');
		echo json_encode(
			array(
				'status'=>1,
				'message'=>$aeSettings->getText('settings_saved')
				)
			);

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
