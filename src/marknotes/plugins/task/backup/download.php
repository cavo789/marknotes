<?php
/**
 * Download the ZIP
 */
namespace MarkNotes\Plugins\Task\Backup;

defined('_MARKNOTES') or die('No direct access allowed');

class Download extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.backup';
	protected static $json_options = 'plugins.options.task.backup';

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if (self::isEnabled(true)) {

			$aeFunctions = \MarkNotes\Functions::getInstance();

			$filename = trim(urldecode($aeFunctions->getParam('file', 'string', '', true)));

			$filename = trim($filename, '"');

			if ($filename == '') {
				$filename = $aeSession->get('backup_zipfilename', '').'_000.zip';
			}

			$arr = $aeSettings->getPlugins(self::$json_options);

			// Get the root folder
			$root = $aeSettings->getFolderWebRoot();

			// Get the location of the backup folder
			$backup_folder = $arr['folder']??'backup';
			$backup_folder = rtrim($backup_folder, DS);
			// And make the folder name absolute
			$backup_folder = $root.$backup_folder.DS;

			$zip_filename = $backup_folder.$filename;

			if ($aeFiles->exists($zip_filename)) {
				// Download the ZIP file
				$aeDownload = \MarkNotes\Tasks\Download::getInstance();
				$aeDownload->run($zip_filename, 'zip');
			} else {
				// The zip was not found
				$status = 0;
				$message = $aeSettings->getText('file_not_found');
				$message = str_replace('$1', $filename, $message);
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
