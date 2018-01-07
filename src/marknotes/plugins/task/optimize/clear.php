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
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if (boolval($aeSession->get('authenticated', 0))) {

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("Clear","debug");
			}
			/*<!-- endbuild -->*/

			// Clear the cache
			$aeCache = \MarkNotes\Cache::getInstance();
			$aeCache->clear();

			// When the task is 'clear', just clear the session
			$aeSession->destroy();

			header('Content-Type: application/json');
			echo json_encode(array('status' => '1'));
		} else {
			// The user isn't logged in, he can't modify settings

			header('Content-Type: application/json');
			echo json_encode(
				array(
					'status'=>0,
					'message'=>$aeSettings->getText('not_authenticated')
					)
				);
		}

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
