<?php
/**
 * Rename a folder
 *
 * Anwser to URL like the one below (names are base64_encoded)
 * index.php?task=task.folder.rename&oldname=JTJGemEy&newname=JTJGenp6enp6
 */
namespace MarkNotes\Plugins\Task\Folder;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(__FILE__).DS.'.plugin.php');

class Rename extends \MarkNotes\Plugins\Task\Folder
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.folder';
	protected static $json_options = '';

	/**
	 * Rename an existing folder
	 */
	private static function rename(string $oldname, string $newname) : float
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if ((trim($oldname) === '') || (trim($newname) === '')) {
			return FILE_ERROR;
		}

		// Sanitize foldersname
		$oldname = $aeFiles->sanitizeFileName($oldname);
		$oldname = $aeSettings->getFolderDocs().$oldname;

		$newname = $aeFiles->sanitizeFileName($newname);
		$newname = $aeSettings->getFolderDocs().$newname;

		// Try to remove a file, first, be sure that the user
		// can see the parent folder : if he can't, he can't delete the file
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('task.acls.cansee');

		// Note : the folder should start and end with the slash
		$arr = array('folder' => $oldname,'return' => true);
		$args = array(&$arr);

		$aeEvents->trigger('task.acls.cansee::run', $args);

		// cansee will initialize return to 0 if the user can't
		// see the folder so can't see the note too
		if (intval($args[0]['return'])===1) {
			// Only if the user can see the folder, he can rename it
			if (!$aeFiles->folderExists($oldname)) {
				// The "old" folder is not found
				return FOLDER_NOT_FOUND;
			} else {
				if ($aeFiles->folderExists($newname)) {
					// The new folder already exists
					return ALREADY_EXISTS;
				} else {
					try {
						rename(mb_convert_encoding($oldname, "ISO-8859-1", "UTF-8"), mb_convert_encoding($newname, "ISO-8859-1", "UTF-8"));

						return ($aeFiles->folderExists($newname) ? RENAME_SUCCESS : FILE_ERROR);
					} catch (Exception $ex) {
						/*<!-- build:debug -->*/
						if ($aeSettings->getDebugMode()) {
							$aeDebug = \MarkNotes\Debug::getInstance();
							$aeDebug->log($ex->getMessage(), 'error');
						}
						/*<!-- endbuild -->*/

						return FILE_ERROR;
					} // try
				} // if ($aeFiles->folderExists($newname))
			} // if ($aeFiles->folderExists($newname))
		} else { // if (intval($args[0]['return'])===1)
			return NO_ACCESS;
		}
	}

	/**
	 * Rename a folder on the disk
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Be sure that filenames doesn't already start with the /docs folder
		self::cleanUp($params, $aeSettings->getFolderDocs(false));

		$newname = trim(urldecode($aeFunctions->getParam('param', 'string', '', true)));
		if ($newname != '') {
			$newname = $aeFiles->sanitizeFileName(trim($newname));
		}

		$oldname = trim(urldecode($aeFunctions->getParam('oldname', 'string', '', true)));

		if ($oldname != '') {
			$oldname = $aeFiles->sanitizeFileName(trim($oldname));
		}

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log(__METHOD__, 'debug');
			$aeDebug->log('Oldname=['.$oldname.']', 'debug');
			$aeDebug->log('Newname=['.$newname.']', 'debug');
		}
		/*<!-- endbuild -->*/

		if (trim($newname) === '') {
			$return = array(
				'status' => 0,
				'action' => 'rename',
				'msg' => $aeSettings->getText('unknown_error', 'An error has occured, please try again')
			);
		} else {
			$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

			// Relative foldernames
			$rel_oldname = str_replace($aeSettings->getFolderDocs(true), '', $oldname);
			$rel_newname = str_replace($aeSettings->getFolderDocs(true), '', $newname);

			// Try to create a file called "$filename.md" on the disk
			$wReturn = self::rename($oldname, $newname);

			switch ($wReturn) {
				case RENAME_SUCCESS:
					$msg = $aeSettings->getText('folder_renamed', 'The folder [$1] has been renamed into [$2]');
					$msg = str_replace('$1', $rel_oldname, $msg);
					$msg = str_replace('$2', $rel_newname, $msg);
					break;
				case NO_ACCESS:
				// The parent folder is protected and the user has no access to it
				$msg = $aeSettings->getText('folder_parent_not_accessible', 'The parent folder of [$1] is not accessible to you');
				$msg = str_replace('$1', $rel_oldname, $msg);
				break;
				case FOLDER_NOT_FOUND:
					$msg = $aeSettings->getText('folder_not_found', 'The folder [$1] doesn\\&#39;t exists');
					$msg = str_replace('$1', $rel_oldname, $msg);
					break;
				default:
					$msg = $aeSettings->getText('error_rename_folder', 'An error has occured when trying to rename the folder [$1] into [$2]');
					$msg = str_replace('$1', $rel_oldname, $msg);
					$msg = str_replace('$2', $rel_newname, $msg);
					break;
			}

			$arr = array(
				'status' => (($wReturn == RENAME_SUCCESS) ? 1 : 0),
				'action' => 'rename',
				'md5' => md5($docs.$newname),
				'msg' => $msg,
				'foldername' => utf8_encode($newname)
			);
			$return =  self::returnInfo($arr);
		}

		header('Content-Type: application/json');
		echo $return;

		return true;
	}
}
