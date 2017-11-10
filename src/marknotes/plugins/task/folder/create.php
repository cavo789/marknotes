<?php
/**
 * Create a folder
 *
 * Anwser to URL like the one below (names are base64_encoded)
 * index.php?task=task.folder.create&param=JTJGenp6enp6
 */
namespace MarkNotes\Plugins\Task\Folder;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(__FILE__).DS.'.plugin.php');

class Create extends \MarkNotes\Plugins\Task\Folder
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.folder';
	protected static $json_options = '';

	/**
	 * Create a new folder
	 */
	public static function create(string $foldername) : float
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if (trim($foldername) === '') {
			return FILE_ERROR;
		}

		// Sanitize the foldername
		$foldername = $aeFiles->sanitizeFileName($foldername);

		// Try to remove the folder, first, be sure that the user
		// can see the folder : if he can't, he can't delete it too
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('task.acls.cansee');

		// Note : the folder should start and end with the slash
		$arr = array('folder' => $foldername,'return' => true);
		$args = array(&$arr);

		$aeEvents->trigger('task.acls.cansee::run', $args);

		// cansee will initialize return to 0 if the user can't
		// see the folder
		if (intval($args[0]['return'])===1) {
			// Only if the user can see the parent folder, he can create a child folder

			if ($aeFiles->folderExists($foldername)) {
				// The folder already exists
				return ALREADY_EXISTS;
			} elseif (!$aeFiles->folderExists(dirname($foldername))) {
				// The parent folder doesn't exists
				return FOLDER_NOT_FOUND;
			} else {
				if (!$aeFiles->folderExists($foldername)) {
					try {
						mkdir(mb_convert_encoding($foldername, "ISO-8859-1", "UTF-8"), CHMOD_FOLDER);
						return ($aeFiles->folderExists($foldername) ? CREATE_SUCCESS : FILE_ERROR);
					} catch (Exception $ex) {
						/*<!-- build:debug -->*/
						if ($aeSettings->getDebugMode()) {
							$aeDebug = \MarkNotes\Debug::getInstance();
							$aeDebug->log($ex->getMessage(), 'error');
						}
						/*<!-- endbuild -->*/

						return FILE_ERROR;
					} // try
				} // if (!$aeFiles->folderExists($foldername))
			} // if ($aeFiles->folderExists($foldername))
		} else {
			// The parent folder is protected so the user can't create a
			// subfolder
			return NO_ACCESS;
		}
	}

	/**
	 * Create a folder on the disk
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Be sure that filenames doesn't already start with the /docs folder
		self::cleanUp($params, $aeSettings->getFolderDocs(false));

		// The folder name is stored in $params['filename']
		$foldername = trim($params['filename']);
		if ($foldername != '') {
			$foldername = $aeFiles->sanitizeFileName(trim($foldername));
		}

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log(__METHOD__, 'debug');
			$aeDebug->log('Newname=['.$foldername.']', 'debug');
		}
		/*<!-- endbuild -->*/

		if (trim($foldername) === '') {
			$return = array(
				'status' => 0,
				'action' => 'create',
				'msg' => $aeSettings->getText('unknown_error', 'An error has occured, please try again')
			);
		} else {
			$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

			// Relative foldernames
			$rel_newname = str_replace($aeSettings->getFolderDocs(true), '', $foldername);

			$wReturn = self::create($foldername);

			switch ($wReturn) {
				case CREATE_SUCCESS:
					$msg = $aeSettings->getText('folder_created', 'The folder [$1] has been created on the disk');
					$msg = str_replace('$1', $rel_newname, $msg);
					break;
				case NO_ACCESS:
					// The parent folder is protected and the user has no access to it
					$msg = $aeSettings->getText('folder_parent_not_accessible', 'The parent folder of [$1] is not accessible to you');
					$msg = str_replace('$1', $rel_newname, $msg);
					break;
				case FOLDER_NOT_FOUND:
					// The parent folder seems to be missing (renamed outside marknotes?)
					$msg = $aeSettings->getText('folder_not_found', 'The folder [$1] doesn\\&#39;t exists');
					$msg = str_replace('$1', $rel_newname, $msg);
					break;
				case ALREADY_EXISTS:
					$msg = $aeSettings->getText('file_already_exists');
					$msg = str_replace('$1', $rel_newname, $msg);
					break;
				default:
					$msg = $aeSettings->getText('error_create_folder');
					$msg = str_replace('$1', $rel_newname, $msg);
					break;
			}

			$return = array(
				'status' => (($wReturn == CREATE_SUCCESS) ? 1 : 0),
				'action' => 'create',
				'md5' => md5($docs.$foldername),
				'msg' => $msg,
				'foldername' => utf8_encode($foldername)
			);
		}

		header('Content-Type: application/json');
		echo self::returnInfo($return);

		return true;
	}
}
