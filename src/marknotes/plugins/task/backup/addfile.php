<?php
/**
 * Add a file to the archive
 */
namespace MarkNotes\Plugins\Task\Backup;

defined('_MARKNOTES') or die('No direct access allowed');

class Addfile extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.backup';
	protected static $json_options = '';

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$status = 0;

		if (self::isEnabled(true)) {
			$lib = __DIR__.DS.'helper/grabber.php';

			// Be sure the library is there
			if ($aeFiles->exists($lib)) {
				require_once($lib);
				$grabber = new \MarkNotes\Grabber();
				$grabber->process();
			} else {
				$message = $aeSettings->getText("error_lib_not_found");
				$message = str_replace('$1', 'Grabber', $message);
			}

		} else { // if (self::isEnabled(true))
			$status = 0;
			$message = $aeSettings->getText('not_authenticated');
		} // if (self::isEnabled(true))

		header('Content-Type: application/json');
		echo json_encode(array('status'=>$status,'message'=>$message));

		return true;
	}
}
