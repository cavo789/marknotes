<?php
/**
 * Debuging - Kill the debug file so the next time
 *
 * 	NOTE : MARKNOTES ALWAYS OUTPUT IN THE DEBUG FILE SO
 * 	KILLING THE FILE WILL WORKS BUT THE FILE WILL BE
 * 	AS SOON AS CREATED AGAIN TO OUTPUT THE task.debug.clear
 * 	DEBUG INFO
 *
 * Answer to URL index.php?task=task.debug.clear
 */
namespace MarkNotes\Plugins\Task\debug;

defined('_MARKNOTES') or die('No direct access allowed');

class clear extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.debug.clear';
	protected static $json_options = 'plugins.options.task.debug.clear';

	public static function run(&$params = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();

		$sFileName = $aeDebug->getLogFilename();

		if ($aeFiles->exists($sFileName)) {
			$aeFiles->delete($sFileName);
			$msg = 'file_deleted';
		} else {
			$msg = 'file_not_found';
		}

		$msg = $aeSettings->getText($msg, '');
		$msg = str_replace('$1', $sFileName, $msg);

		$return = array();
		$return['status'] = 1;
		$return['message'] = $msg;

		header('Content-Type: application/json');
		echo json_encode($return, JSON_PRETTY_PRINT);

		return true;
	}

	/**
	* Determine if this plugin is needed or not
	*/
	final protected static function canRun() : bool
	{
		$bCanRun = false;

		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// Both Debug and DevMode should be enabled
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$bCanRun = $aeDebug->getDevMode();
			}
		}

		if (!$bCanRun) {
			/*<!-- endbuild -->*/
			$return = array();
			$return['status'] = 0;
			$return['message'] = $aeSettings->getText('only_dev_mode_on', '', true);

			header('Content-Type: application/json');
			echo json_encode($return, JSON_PRETTY_PRINT);
			/*<!-- build:debug -->*/
		}

		return $bCanRun;
	}
}
