<?php
/**
 * Create a file
 *
 * Anwser to URL like the one below (names are base64_encoded)
 * index.php?task=task.file.create&param=enp6enp6JTJGYWRm
 */

namespace MarkNotes\Plugins\Task\File;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(__FILE__).DS.'.plugin.php');

class Create extends \MarkNotes\Plugins\Task\File
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.file';
	protected static $json_options = '';

	/**
	 * Create a new file
	 */
	private static function create(string $filename) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

		// Sanitize the filename
		$filename = $aeFiles->sanitize($filename);

		// Try to remove the folder, first, be sure that the user
		// can see the folder : if he can't, he can't delete it too
		$aeEvents = \MarkNotes\Events::getInstance();

		$aeEvents->loadPlugins('task.acls.cansee');

		// Note : the folder should start and end with the slash
		$arr = array('folder' => dirname($filename),'return' => true);

		$args = array(&$arr);
		$aeEvents->trigger('task.acls.cansee::run', $args);

		// cansee will initialize return to 0 if the user can't
		// see the folder
		if (intval($args[0]['return'])===1) {
			// Only if the user can see the parent folder, he can
			// create a file

			if (!$aeFiles->exists($filename)) {
				// Define the content : get the filename without the
				// extension and set the content as heading 1.
				// Don't use PHP_EOL but well PHP_LF

				$content = '# '.basename($aeFiles->removeExtension($filename)).PHP_LF;

				$wReturn = ($aeFiles->create($filename, $content, CHMOD_FILE) ? CREATE_SUCCESS : FILE_ERROR);

				// Remember the last added note (f.i. /subfolder/note)
				// (with the extension)
				if ($wReturn === CREATE_SUCCESS) {
					$aeSession = \MarkNotes\Session::getInstance();
					$aeSession->set('last_added_note', basename($aeFiles->removeExtension($filename)));
				}
			} else {
				// The file already exists
				$wReturn =  ALREADY_EXISTS;
			}
		} else {
			// The parent folder is protected so the user can't create a
			// new note in that folder
			$wReturn = NO_ACCESS;
		}

		return $wReturn;
	}

	/**
	 * Create a file on the disk
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Be sure that filenames doesn't already start
		// with the /docs folder
		self::cleanUp($params, $aeSettings->getFolderDocs(false));

		// The folder name is stored in $params['filename']
		$filename = trim($params['filename']);

		if ($filename != '') {
			$filename = $aeFiles->sanitize(trim($filename));
		}

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log(__METHOD__, 'debug');
			$aeDebug->log('Newname=['.$filename.']', 'debug');
		}
		/*<!-- endbuild -->*/

		if (trim($filename) === '') {
			$return = array(
				'status' => 0,
				'action' => 'create',
				'msg' => $aeSettings->getText('unknown_error', 'An error has occured, please try again')
			);
		} else {
			$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));
			if (!$aeFunctions->endsWith($filename, '.md')) {
				$filename.='.md';
			}
			// Be sure to have the .md extension
			$wReturn = self::create($filename);

			// Relative filename; needed for the md5() function
			// since the treeview is storing ID for each items
			// this way : docs/subfolder/note.md
			// The md5 returned below should do the same
			$rel_newname = str_replace($aeSettings->getFolderDocs(true), $docs, $filename);

			// and remove the extension
			$rel_newname = $aeFiles->removeExtension($rel_newname);

			// The filename should be something like
			// docs\christophe\note
			// (not docs\christophe\note.md)

			switch ($wReturn) {
				case CREATE_SUCCESS:
					$msg = $aeSettings->getText('file_created', 'The file [$1] has been created on the disk');
					$msg = str_replace('$1', $rel_newname, $msg);
					break;

				case NO_ACCESS:
					// The parent folder is protected and the user has no access to it
					$msg = $aeSettings->getText('folder_parent_not_accessible', 'The parent folder of [$1] is not accessible to you');
					$msg = str_replace('$1', $rel_newname, $msg);
					break;

				case ALREADY_EXISTS:
					$msg = $aeSettings->getText('file_already_exists');
					$msg = str_replace('$1', $rel_newname, $msg);
					break;

				default:
					$msg = $aeSettings->getText('error_create_file');
					$msg = str_replace('$1', $rel_newname, $msg);
					break;
			}

			$return = array(
				'status' => (($wReturn == CREATE_SUCCESS) ? 1 : 0),
				'action' => 'create',
				'md5' => md5($rel_newname),
				'msg' => $msg,
				'filename' => utf8_encode($filename)
			);
		}

		header('Content-Type: application/json');
		echo self::returnInfo($return);

		return true;
	}
}
