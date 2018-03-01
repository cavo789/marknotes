<?php
/**
 * Delete a file.
 *
 * Anwser to URL like the one below (names are base64_encoded)
 * index.php?task=task.file.delete&oldname=emEyJTJGYQ%3D%3D&newname=emEyJTJGYWVyYXpl
 */
namespace MarkNotes\Plugins\Task\File;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(__FILE__).DS.'.plugin.php');

class Delete extends \MarkNotes\Plugins\Task\File
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.file';
	protected static $json_options = '';

	/**
	 * Delete an existing note
	 */
	private static function delete(string $filename) : float
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if (trim($filename) === '') {
			return FILE_ERROR;
		}

		// Try to remove a file, first, be sure that the user
		// can see the parent folder : if he can't, he can't
		// delete the file
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('task.acls.cansee');

		// Note : the folder should start and end with the slash
		$arr = array('folder' => dirname($filename),'return' => true);
		$args = array(&$arr);

		$aeEvents->trigger('task.acls.cansee::run', $args);

		// cansee will initialize return to 0 if the user can't
		// see the folder so can't see the note too
		if (intval($args[0]['return'])===1) {
			// Continue only if the user can see the parent folder

			if (!$aeFiles->exists($filename)) {
				return FILE_NOT_FOUND;
			} elseif (!is_writable(mb_convert_encoding($filename, "ISO-8859-1", "UTF-8"))) {
				return FILE_IS_READONLY;
			} else {
				// Before removing the file (f.i. note.md),
				// check if we've another files with the same
				// name but with other extensions (like note.json,
				// note.html, ...) and remove them too.
				$name = $aeFiles->removeExtension(basename($filename));
				$arrFiles = $aeFolders->getContent(dirname($filename));

				$docs = $aeSettings->getFolderDocs(true);

				$wReturn = true;
				foreach ($arrFiles as $file) {

					if ($file['type'] == 'file') {
						if ($file['filename'] == $name) {
							try {
								// delete() require an
								// absolute filename
								$tmp = $aeFiles->makeFileNameAbsolute($file['path']);

								$aeFiles->delete($tmp);

								$wReturn = (!$aeFiles->exists($file['path']) ? KILL_SUCCESS : FILE_ERROR);

							} catch (Exception $ex) {

								/*<!-- build:debug -->*/
								if ($aeSettings->getDebugMode()) {
									$aeDebug = \MarkNotes\Debug::getInstance();
									$aeDebug->log($ex->getMessage(), 'error');
								}
								/*<!-- endbuild -->*/
								$wReturn = FILE_ERROR;
							} // try
						}
					}
				} // foreach
			}
		} else {
			// The folder is protected and the user can't see it.
			$wReturn = NO_ACCESS;
		}

		return $wReturn;
	}

	/**
	 * Delete a note on the disk
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Be sure that filenames doesn't already start with
		// the /docs folder
		self::cleanUp($params, $aeSettings->getFolderDocs(false));

		$oldname = trim(urldecode($aeFunctions->getParam('oldname', 'string', '', true)));

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log(__METHOD__, 'debug');
			$aeDebug->log('Oldname=['.$oldname.']', 'debug');
		}
		/*<!-- endbuild -->*/

		if (trim($oldname) === '') {
			$return = array(
				'status' => 0,
				'action' => 'delete',
				'msg' => $aeSettings->getText('unknown_error', 'An error has occured, please try again')
			);
		} else {
			$docs = $aeSettings->getFolderDocs(false);

			$oldname = $aeFiles->sanitize($oldname);
			$oldname = $aeSettings->getFolderWebRoot().$oldname;
			$oldname = $aeFiles->removeExtension($oldname).'.md';
			$oldname = str_replace('/', DS, $oldname);

			// Relative filenames
			$rel_oldname = str_replace($aeSettings->getFolderDocs(true), '', $oldname);

			// Try to create a file called "$filename.md" on the disk
			$wReturn = self::delete($oldname);

			switch ($wReturn) {
				case KILL_SUCCESS:
					$msg = $aeSettings->getText('file_deleted', 'The note [$1] has '.
						'been successfully deleted');
					$msg = str_replace('$1', $rel_oldname, $msg);
					break;
				case NO_ACCESS:
					$msg = $aeSettings->getText('file_cant_delete', 'You can\'t delete the file [$1] since you don\'t have the right to see the parent folder');
					$msg = str_replace('$1', $rel_oldname, $msg);
					break;
				case FILE_NOT_FOUND:
					$msg = $aeSettings->getText('file_not_found', 'The note [$1] doesn\\&#39;t exists');
					$msg = str_replace('$1', $rel_oldname, $msg);

					break;
				case FILE_IS_READONLY:
					$msg = $aeSettings->getText('file_read_only', 'The note [$1] is read-only, it\\&#39;s then impossible to delete it');
					$msg = str_replace('$1', $rel_oldname, $msg);
					break;
				default:
					$msg = $aeSettings->getText('error_delete_file', 'An error has occured during the deletion of the note [$1]');
					$msg = str_replace('$1', $rel_oldname, $msg);
					break;
			}

			// When killing a file, the treeview should be opened
			// to the parent folder. The md5 should then be
			// calculated on the parent folder of the removed
			// note.
			$md5 = md5(dirname($docs.$rel_oldname).DS);

			$arr = array(
				'status' => (($wReturn == KILL_SUCCESS) ? 1 : 0),
				'action' => 'delete',
				'msg' => $msg,
				'md5' => $md5,
				'filename' => utf8_encode($oldname)
			);

			$return =  self::returnInfo($arr);
		}

		header('Content-Type: application/json');
		echo $return;

		return true;
	}
}
