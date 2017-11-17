<?php
/**
 * Build the JSON answer required by jsTree in order to display the
 * list of folders (empty folder are taken) and files.
 *
 * The list will NOT include protected folders i.e. when the ACLs plugin
 * is enabled and configured for not showing somes folders to everyone.
 *
 * When the visitor isn't allowed to see a folder, that folder won't appear
 * in the JSON answer
 *
 * Answer to /index.php?task=task.listfiles.treeview or the "fake"
 * file /listfiles.json
 */
namespace MarkNotes\Plugins\Task\ListFiles;

defined('_MARKNOTES') or die('No direct access allowed');

class Treeview extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.listfiles';
	protected static $json_options = 'plugins.options.task.listfiles';

	private static $bACLsLoaded = 0;

	/**
	* Called by ListFiles().  Populate an array with the list of .md files.
	* The structure of the array match the needed definition of the jsTree
	* jQuery plugin
	* http://stackoverflow.com/a/23679146/1065340
	*
	* @param  type   $dir   Root folder to scan
	* @return array
	*/
	private static function makeJSON(string $dir) : array
	{
		static $index = 0;

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		/**
		* @TODO: Understand why on somes Windows computer (my home one),
		* this function shouldn't use utf8_encode() for returning
		* files/folders name but on some other (my office computer) yes,
		* utf8_encode should be used. Strange! June 2017 : for the moment,
		* I've created a files->encode_accent option in the settings.json
		* file. By default, set to 0.
		**/
		$arr = $aeSettings->getPlugins('/files', array('encode_accent'=>0));
		$bEncodeAccents = boolval($arr['encode_accent']);

		$root = str_replace('/', DS, $aeSettings->getFolderDocs(true));
		$rootNode = $aeSettings->getFolderDocs(false);

		// Get the list of files and folders for the treeview
		$arrEntries = self::directoryToArray($dir);

		// Now, prepare the JSON return
		$sDirectoryText = basename($dir);
		$sID = str_replace($root, '', $dir).DS;

		// It's a folder node
		$dataURL=str_replace(DS, '/', str_replace($root, '', $dir));
		$dataURL.=(($root == $dir)?'':'/').'index.html';

		if ($bEncodeAccents) {
			$sDirectoryText = utf8_encode($sDirectoryText);
			$sID = utf8_encode(str_replace($root, '', $dir).DS);
			$dataURL = utf8_encode($dataURL);
		}

		$listDir = array
		(
			'id' => str_replace(DS, '/', $sID),
			'type' => 'folder',
			'icon' => 'folder',
			'text' => str_replace(DS, '/', $sDirectoryText),
			'state' => array(
				'opened' => (($root == $dir)?1:0), // Opened only if the top root folder
				'disabled' => 1
			),
			'data' => array(
				//'task' => 'display',
				// Right clic on the node ? Open the_folder/index.html page
				'url' => $dataURL
			),
			'children' => array()
		);

		$dirs = array();
		$files = array();

		foreach ($arrEntries as $entry) {
			if ($entry['type'] == 'file') {
				// We're processing a filename

				$index += 1;

				//Filename but without the extension (and no path)
				$filename = str_replace('.md', '', basename($entry['name']));

				// Relative filename like f.i.  docs/the_folder/a_note.md
				$id = str_replace($root, $rootNode, $entry['name']);

				// Right-click on a file = open it's HTML version
				$dataURL=str_replace($root, '', $entry['name']);
				$dataURL=str_replace(DS, '/', $dataURL);
				$dataURL=str_replace('.md', '.html', $dataURL);

				$sFileText = $filename;

				// If the title is really long, …
				// 30 characters in the treeview are enough
				if (strlen($sFileText) > TREEVIEW_MAX_FILENAME_LENGTH) {
					/* We'll truncate the filename to only the first ...
					  thirty ... characters
					  But, special case, when the filename is truncated,
					  if the very last position is an accentuated char.,
					  we can't truncate exactly at that size because such
					  character is on two bytes. We can only keep the first
					  one otherwise we'll have an encoding problem.
					  So, in this case, truncate one more char (so keep
					  29 f.i.)
					*/
					try {
						$wLen = TREEVIEW_MAX_FILENAME_LENGTH;
						$tmp = json_encode(substr($sFileText, 0, $wLen));
						if (json_last_error()===JSON_ERROR_UTF8) {
							$wLen--;
							$tmp = json_encode(substr($sFileText, 0, $wLen));
						}

						$sFileText = substr($sFileText, 0, $wLen).' …';
					} catch (Exception $e) {
						/*<!-- build:debug -->*/
						die("<pre style='background-color:yellow;'>".__FILE__." - ".__LINE__." ".print_r($sFileText, true)."</pre>");
						/*<!-- endbuild -->*/
					}
				}

				$dataFile = str_replace($root, '', $entry['name']);
				$dataFile = str_replace(DS, '/', $dataFile);

				if ($bEncodeAccents) {
					$dataFile = utf8_encode($dataFile);
					$dataURL = utf8_encode($dataURL);
					$sFileText = utf8_encode($sFileText);
				}

				$dataBasename = $aeFiles->removeExtension(basename($dataURL));

				//if ($bEncodeAccents) {
					// Use utf8_encode only under Windows OS
				//	$id = utf8_encode($id);
				//}

				$default_task = 'task.export.html';

				// In the list of files, help the jsTree plugin
				// to know that the action should be EDIT and not DISPLAY
				// when the user click on the note that was just created
				$lastAddedNote = trim($aeSession->get('last_added_note', ''));
				if ($dataBasename===$lastAddedNote) {
					$default_task = 'task.edit.form';
				}

				$files[] = array(
					'id' => md5($id),
					'icon' => 'file file-md',
					'text' => $sFileText,
					'data' => array(
						//'task' => 'display',
						'basename' => $dataBasename,
						'task' => $default_task,
						'file' => $dataFile,
						'url' => $dataURL
					),
					'state' => array(
						'opened' => 0,   // A file isn't opened
						'selected' => 0  // and isn't selected by default
					)
				);
			} elseif ($entry['type'] == 'folder') {
				// It's a folder

				// Check if the folder can be displayed or not
				//$aeEvents->loadPlugins('task', 'acls');

				$params = '';

				// Derive the folder name
				// From c:/sites/marknotes/docs/the_folder, keep /the_folder/
				$fname = str_replace($root, '', $entry['name']);
				$fname = DS.ltrim(rtrim($fname, DS), DS).DS;

				// The folder should start and end with the slash
				// so "/the_folder/" and not something else.
				$tmp = array(
					'folder' => rtrim($root, DS).$fname,
					'return' => true
				);
				$args = array(&$tmp);

				// If the task.acls.cansee wasn't fired i.e. when there was
				// no folder to protect, the trigger even return False.
				// Otherwise, trigger return True : the plugin has been fired
				if (static::$bACLsLoaded) {
					$bReturn = $aeEvents->trigger('task.acls.cansee::run', $args);
				} else {
					// ACLs plugin not loaded; every files / folders can be
					// accessed
					$args[0]['return'] = 1;
					$bReturn = true;
				}

				if ($bEncodeAccents) {
					$fname = utf8_encode($fname);
				}

				// The canSeeFolder event will initialize the 'return'
				// parameter to false when the current user can't see the
				// folder i.e. don't have the permission to see it. This
				// permission is defined in the acls plugin options
				//
				// See function run() of
				// MarkNotes\Plugins\Task\ACLs\cansee.php
				// for more information
				//
				// $bReturn === false ==> there was no protected folder.
				if (($bReturn===false) || ($args[0]['return'] === 1)) {
					$dirs [] = $entry['name'];
				}
			} // if ($entry['type']=='folder')
		} // foreach

		// The current folder has been processed, are there subfolders in it ?
		if (count($dirs) > 0) {
			foreach ($dirs as $d) {
				list($arrChildren, $tmp) = self::makeJSON($d);
				$listDir['children'][] = $arrChildren;
			}
		}

		foreach ($files as $file) {
			$listDir['children'][] = $file;
		}

		return array($listDir, $index);
	}

	/**
	* Get an array that represents directory tree
	* @param string $directory   Directory path
	*/
	private static function directoryToArray($directory)
	{
		static $root = '';

		$aeFiles = \MarkNotes\Files::getInstance();

		if ($root === '') {
			$aeSettings = \MarkNotes\Settings::getInstance();
			$root = str_replace('/', DS, $aeSettings->getFolderDocs(true));
		}

		$arr = array();

		if (is_dir($directory)) {
			$handle = opendir($directory);
			if ($handle) {
				while (false !== ($file = readdir($handle))) {
					// Don't take files/folders starting with a dot
					if (substr($file, 0, 1) !== '.') {
						// Absolute filename / foldername
						$name = rtrim($directory, DS).DS.$file;
						if (is_dir($name)) {
							// It's a folder

							$arr[] = array('name' => $name,'type' => 'folder');
						} else {
							// it's a file, get it only if the
							// extension is .md

							$extension = pathinfo($name, PATHINFO_EXTENSION);
							if ($extension==='md') {
								$arr[] = array('name' => $name,'type' => 'file');
							}
						} // if (is_dir($directory.DS.$file))
					} // if (substr($file, 0, 1) !== '.')
				} // while

				closedir($handle);
			} // if ($handle)
		} // if ($aeFiles->folderExists($directory))

		$name = array();

		// Sort the array by name
		foreach ($arr as $key => $row) {
			$name[$key] = $row['name'];
		} // foreach

		array_multisort($name, SORT_ASC | SORT_NATURAL | SORT_FLAG_CASE, $arr);

		return $arr;
	}

	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrSettings = $aeSettings->getPlugins('/interface');
		$show_tree_allowed = boolval($arrSettings['show_tree_allowed'] ?? 1);

		if (!$show_tree_allowed) {
			// The webmaster has disabled to right to see
			// the interface so, it seems coherent to also
			// disable the listfiles task.
			$return = array();
			$return['count'] = 0;
			$return['status'] = 0;
			$return['message'] = 'The administrator has disabled access to the interface.';
			$sReturn = json_encode($return);
		} else {
			// Call the ACLs plugin
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->loadPlugins('task.acls.load');
			$args=array();
			$aeEvents->trigger('task.acls.load::run', $args);

			// $bACLsLoaded will be set true if at least one folder is
			// protected
			static::$bACLsLoaded = boolval($aeSession->get('acls', '') != '');

			$sReturn = '';

			if (!static::$bACLsLoaded) {
				$arrOptimize = $aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);

				$bOptimize = $arrOptimize['server_session'] ?? false;

				if ($bOptimize) {
					$sReturn = trim($aeSession->get('treeview_json', ''));
				}
			} // if (static::!$bACLsLoaded)

			if ($sReturn === '') {
				$docs = $aeSettings->getFolderDocs(true);

				$aeEvents = \MarkNotes\Events::getInstance();
				$aeEvents->loadPlugins('task.acls.cansee');

				// Populate the tree that will be used for jsTree
				// (see https://www.jstree.com/docs/json/)
				list ($arr, $count) = self::makeJSON(str_replace('/', DS, $docs));

				// Build the json
				$return = array();
				//$return['settings'] = array('root' => str_replace(DS, '/',
				// $docs));
				$return['count'] = $count;
				$return['tree'] = $arr;

				$aeJSON = \MarkNotes\JSON::getInstance();
				/*<!-- build:debug -->*/
				$aeJSON->debug($aeSettings->getDebugMode());
				/*<!-- endbuild -->*/

				$sReturn = $aeJSON->json_encode($return);

				if (!static::$bACLsLoaded) {
					if ($bOptimize) {
						// Remember for the next call
						$aeSession->set('treeview_json', $sReturn);
					}
				} // if (!static::$bACLsLoaded)
			} // if ($sReturn === '')
		} // if (!$show_tree_allowed)

		header('Content-Type: application/json; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		die($sReturn);

		return true;
	}
}
