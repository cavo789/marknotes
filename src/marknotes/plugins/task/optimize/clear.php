<?php
/**
 * Optimizations tasks - Clear the session. Called by the 'Eraser' button,
 *    located in the option button of the treeview pane
 */

namespace MarkNotes\Plugins\Task\Optimize;

defined('_MARKNOTES') or die('No direct access allowed');

class Clear
{
	public static function clearSession()
	{
		$aeCache = \MarkNotes\Cache::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log("Clear","debug");
		}
		/*<!-- endbuild -->*/

		// Clear the cache
		$aeCache->clear();

		// When the task is 'clear', just clear the session
		$aeSession->destroy();

		header('Content-Type: application/json');
		echo json_encode(array('status' => '1'));

		return true;
	}

	/**
	 * Attach the function and responds to events
	 */
	public function bind(string $task)
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->bind('run', __CLASS__.'::clearSession', $task);
		return true;
	}
}
