<?php
/**
 * Delete a folder (and all children)
 *
 * Anwser to URL like the one below (names are base64_encoded)
 * index.php?task=task.folder.delete&oldname=enp6enp6JTJGcWZxc2RmcWQ%3D
 */
namespace MarkNotes\Plugins\Task\Folder;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(__FILE__).DS.'.plugin.php');

class Delete extends \MarkNotes\Plugins\Task\Folder
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.folder';
	protected static $json_options = '';

	private static function is_dir_empty($foldername) : bool
	{
		if (!is_readable($foldername)) {
			return false;
		}
		return ((count(scandir($foldername)) == 2) ? true : false);
	}

	/**
	 * Kill a folder recursively
	 */
	private static function delete(string $foldername) : float
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if (trim($foldername) === '') {
			return FILE_ERROR;
		}

		// Sanitize foldersname
		$foldername = $aeFiles->sanitizeFileName($foldername);
		$foldername = str_replace('/', DS, $aeSettings->getFolderDocs().$foldername);

		if (!$aeFiles->folderExists($foldername)) {
			// The folder is not found
			return FOLDER_NOT_FOUND;
		} else {
			// $foldername will be something like c:\websites\notes\docs\folder\folder_to_kill
			// So be really sure that the $foldername absolute path is well within the $docs
			// folder (strcmp should strictly give 0).  if so, continue and allow the deletion
			// If not, stop and return an error.

			$docs = $aeSettings->getFolderDocs(true);

			if (strcmp($docs, substr($foldername, 0, strlen($docs))) !== 0) {
				// Outside the /docs folder, prohibited
				return FOLDER_NOT_DELETED;
			} elseif (!is_writable(mb_convert_encoding($foldername, "ISO-8859-1", "UTF-8"))) {
				// Don't start and kill files if the folder is read-only
				return FOLDER_IS_READONLY;
			} else {
				// Ok, recursively kill the folder and its content

				$it = new \RecursiveDirectoryIterator(mb_convert_encoding($foldername, "ISO-8859-1", "UTF-8").DS, \RecursiveDirectoryIterator::SKIP_DOTS);

				$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
				foreach ($files as $file) {
					$name = utf8_encode($file->getRealPath());

					if ($file->isDir()) {
						/*<!-- build:debug -->*/
						if ($aeSettings->getDebugMode()) {
							$aeDebug->log('Killing folder ['.utf8_encode($name).']', 'debug');
						}
						/*<!-- endbuild -->*/
						if (is_writable(mb_convert_encoding($name, "ISO-8859-1", "UTF-8"))) {
							try {
								if (self::is_dir_empty($name)) {
									@rmdir($name);
								} else {
									/*<!-- build:debug -->*/
									if ($aeSettings->getDebugMode()) {
										$aeDebug->log($name.' isn\'t empty', 'debug');
									}
									/*<!-- endbuild -->*/
								}
							} catch (Exception $ex) {
								/*<!-- build:debug -->*/
								if ($aeSettings->getDebugMode()) {
									$aeDebug->log($ex->getMessage(), 'error');
								}
								/*<!-- endbuild -->*/
							}
						}

						if ($aeFiles->folderExists($name)) {
							// Still exists

							/*<!-- build:debug -->*/
							if ($aeSettings->getDebugMode()) {
								$aeDebug->log('   Error, folder ['.utf8_encode($name).'] still present', 'debug');
							}
							/*<!-- endbuild -->*/
						}
					} else { // if ($file->isDir())
						/*<!-- build:debug -->*/
						if ($aeSettings->getDebugMode()) {
							$aeDebug->log('Killing file ['.utf8_encode($name).']', 'debug');
						}
						/*<!-- endbuild -->*/

						if (is_writable(mb_convert_encoding($name, "ISO-8859-1", "UTF-8"))) {
							unlink(mb_convert_encoding($name, "ISO-8859-1", "UTF-8"));
						}

						if ($aeFiles->fileExists($name)) {
							/*<!-- build:debug -->*/
							if ($aeSettings->getDebugMode()) {
								$aeDebug->log('   Error, file ['.utf8_encode($name).'] still present', 'debug');
							}
							/*<!-- endbuild -->*/
						}
					} // if ($file->isDir())
				} // foreach

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug->log('Killing file ['.utf8_encode($foldername).']', 'debug');
				}
				/*<!-- endbuild -->*/

				// And kill the folder itself
				try {
					if (self::is_dir_empty(mb_convert_encoding($foldername, "ISO-8859-1", "UTF-8"))) {
						rmdir(mb_convert_encoding($foldername, "ISO-8859-1", "UTF-8"));
					}
				} catch (Exception $ex) {
					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug->log($ex->getMessage(), 'error');
					}
					/*<!-- endbuild -->*/
				}

				if ($aeFiles->folderExists(mb_convert_encoding($foldername, "ISO-8859-1", "UTF-8"))) {
					// Still exists

					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug->log('   Error, folder ['.utf8_encode($foldername).'] still present', 'debug');
					}
					/*<!-- endbuild -->*/

					return FILE_ERROR;
				} else { // if ($aeFiles->folderExists($foldername))
					return KILL_SUCCESS;
				}
			}
		} // if (!$aeFiles->folderExists($foldername))
	}

	/**
	 * Delete a folder on the disk
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Be sure that filenames doesn't already start with the /docs folder
		self::cleanUp($params, $aeSettings->getFolderDocs(false));

		$foldername = trim(urldecode($aeFunctions->getParam('oldname', 'string', '', true)));

		if ($foldername != '') {
			$foldername = $aeFiles->sanitizeFileName(trim($foldername));
		}

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log(__METHOD__, 'debug');
			$aeDebug->log('Oldname=['.$foldername.']', 'debug');
		}
		/*<!-- endbuild -->*/

		if (trim($foldername) === '') {
			$return = array(
				'status' => 0,
				'action' => 'delete',
				'msg' => $aeSettings->getText('unknown_error', 'An error has occured, please try again')
			);
		} else {
			$docs = $aeSettings->getFolderDocs(false);

			// Relative foldername
			$rel_foldername = str_replace($aeSettings->getFolderDocs(true), '', $foldername);

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
				// Only if the user can see the folder, he can delete it
				$wReturn = self::delete($foldername);
			} else {
				// The folder is protected and the user can't see it.
				$wReturn = NO_ACCESS;
			}

			switch ($wReturn) {
				case KILL_SUCCESS:
					$msg = $aeSettings->getText('folder_deleted', 'The folder [$1] and its content has been deleted');
					$msg = str_replace('$1', $rel_foldername, $msg);
					break;
				case NO_ACCESS:
					$msg = $aeSettings->getText('folder_cant_delete', 'You can\'t delete the folder [$1] since you don\'t have the right to see that folder');
					$msg = str_replace('$1', $rel_foldername, $msg);
					break;
				case FOLDER_NOT_DELETED:
					$msg = $aeSettings->getText('folder_not_deleted', 'The folder [$1] is outside your documentation root folder and therefore will not be deleted');
					$msg = str_replace('$1', $rel_foldername, $msg);
					break;
				case FOLDER_NOT_FOUND:
					$msg = $aeSettings->getText('folder_not_found', 'The folder [$1] doesn\\&#39;t exists');
					$msg = str_replace('$1', $rel_foldername, $msg);
					break;
				case FOLDER_IS_READONLY:
					$msg = $aeSettings->getText('folder_read_only', 'Sorry but '.
					'the folder [$1] is read-only');
					$msg = str_replace('$1', $rel_foldername, $msg);
					break;
				default:
					$msg = $aeSettings->getText('error_delete_folder', 'An error has occured during the deletion of the folder [$1] (this is the case when the folder contains readonly subfolders or notes)');
					$msg = str_replace('$1', $rel_foldername, $msg);
					break;
			}

			$arr = array(
				'status' => (($wReturn == KILL_SUCCESS) ? 1 : 0),
				'action' => 'delete',
				'type' => 'folder',
				'md5' => md5($docs.$foldername),
				'msg' => $msg,
				'foldername' => utf8_encode($foldername)
			);

			$return =  self::returnInfo($arr);
		}

		header('Content-Type: application/json');
		echo $return;

		return true;
	}
}
