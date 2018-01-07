<?php
/**
 * Debug - Show the content of the settings.json, once read by
 * $aeSettings
 *
 * Answer to URL like index.php?task=task.settings.display
 */
namespace MarkNotes\Plugins\Task\Settings;

defined('_MARKNOTES') or die('No direct access allowed');

class Display
{
	public static function run(&$params = null)
	{
		// The update requires to be authenticated
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if (boolval($aeSession->get('authenticated', 0))) {

			// Debugging mode should be set and Development mode too
			/*<!-- build:debug -->*/
			if (!$aeSettings->getDebugMode()) {
				header('Content-Type: application/json');
				echo json_encode(
					array(
						'status'=>0,
						'message'=>'Security measure - To use this '.
							'interface, please enable first the '.
							'debug and development mode in the '.
							'settings.json file'
						)
					);
			} else {
				$aeDebug = \MarkNotes\Debug::getInstance();
				if (!$aeDebug->getDevMode()) {
					header('Content-Type: application/json');
					echo json_encode(
						array(
							'status'=>0,
							'message'=>'Security measure - To use '.
								'this interface, please enable '.
								'first the development mode in the '.
								'settings.json file'
							)
						);
				} else {
					header('Content-Type: application/json');
					echo json_encode(
						$aeSettings->getAll()
						);
				}
			}

			/*<!-- endbuild -->*/

		} else {
			// The user isn't logged in, he can't see settings

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
