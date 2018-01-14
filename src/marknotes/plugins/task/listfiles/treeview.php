<?php
/**
* Build the JSON answer required by jsTree in order to display the
* list of folders (empty folder are taken) and files.
*
* The list will NOT include protected folders i.e. when the ACLs
* plugin is enabled and configured for not showing somes folders to
* everyone.
*
* When the visitor isn't allowed to see a folder, that folder won't
* appear in the JSON answer
*
* Answer to /index.php?task=task.listfiles.treeview or the "fake"
* file /listfiles.json
*/
namespace MarkNotes\Plugins\Task\ListFiles;

defined('_MARKNOTES') or die('No direct access allowed');

// There is a bug with PHP 7.0 and filenames with accent so
// a specific procedure should be written otherwise the JSON
// encoding of the list of files will fails
// https://github.com/php/php-src/blob/PHP-7.1.0beta2/UPGRADING#L321
define('PHP_7_0', ((PHP_MAJOR_VERSION == 7) && (PHP_MINOR_VERSION == 0)));

class Treeview extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.listfiles';
	protected static $json_options = 'plugins.options.task.listfiles';
	private static $bACLsLoaded = 0;

	/**
	* Called by ListFiles().  Populate an array with the list of
	* .md files.
	* The structure of the array match the needed definition
	* of the jsTree jQuery plugin
	* http://stackoverflow.com/a/23679146/1065340
	*
	* @param  type	$dir	Root folder to scan
	* @return array
	*/
	private static function makeJSON(string $dir) : array
	{
		static $index = 0;

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

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

		if (PHP_7_0) {

			// Avoid PHP 7.0.x bug : handle accents
			$arrSettings = $aeSettings->getPlugins('/interface');
			$bConvert  = boolval($arrSettings['accent_conversion']??1);

			if ($bConvert) {
				$sID=iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($sID));

				// accent_conversion in settings.json has
				// been initialized to 1 => make the conversion
				$sDirectoryText=iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($sDirectoryText));

				$dataURL=iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($dataURL));
			}
		}

		$listDir = array
		(
			'id' => str_replace(DS, '/', $sID),
			'type' => 'folder',
			'icon' => 'folder',
			'text' => str_replace(DS, '/', $sDirectoryText),
			'state' => array(
				// Opened only if the top root folder
				'opened' => (($root == $dir)?1:0),
				'disabled' => 1
			),
			'data' => array(
				//'task' => 'display',
				// Right clic on the node ?
				// Open the_folder/index.html page
				'url' => $dataURL
			),
			'children' => array()
		);
		$dirs = array();
		$files = array();
		foreach ($arrEntries as $entry) {

			if ($entry['type'] == 'file') {

				$entry['name'] = str_replace('/', DS, $entry['name']);

				// We're processing a filename
				$index += 1;

				//Filename but without the extension (and no path)
				$filename = str_replace('.md', '', basename($entry['name']));

				// Relative filename like f.i.  docs/the_folder/a_note.md
				$id = str_replace($root, $rootNode, $entry['name']);

				// Right-click on a file = open it's HTML version
				$dataURL=str_replace($root, '', $entry['name']);
				// Should be relative to the /docs folder
				//$dataURL=$aeSettings->getFolderDocs(false).$dataURL;
				$dataURL=str_replace(DS, '/', $dataURL).'.html';

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
				$dataBasename = $aeFiles->removeExtension(basename($dataURL));

				$dataFile = str_replace($root, '', $entry['name']);
				$dataFile = str_replace(DS, '/', $dataFile);

				$default_task = 'task.export.html';

				// In the list of files, help the jsTree plugin
				// to know that the action should be EDIT and not DISPLAY
				// when the user click on the note that was just created
				$lastAddedNote = trim($aeSession->get('last_added_note', ''));

				if ($dataBasename===$lastAddedNote) {
					$default_task = 'task.edit.form';
				}

				if (PHP_7_0) {
					if ($bConvert) {
						// accent_conversion in settings.json has
						// been initialized to 1 => make the conversion
						$sFileText=iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($sFileText));

						$dataBasename=iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($dataBasename));

						$dataFile=iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($dataFile));

						$dataURL=iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($dataURL));
					}
				}

				$files[] = array(
					'id' => md5($id),
					'icon' => 'file file-md',
					'text' => $sFileText,
					'data' => array(
						'basename' => $dataBasename,
						'task' => $default_task,
						'file' => $dataFile,
						'url' => $dataURL
					),
					'state' => array(
						'opened' => 0,	// A file isn't opened
						'selected' => 0  // and isn't selected by default
					)
				);
			} elseif ($entry['type'] == 'folder') {
				// It's a folder

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
				// Otherwise, trigger return True : the plugin has been
				// fired
				if (static::$bACLsLoaded) {
					$bReturn = $aeEvents->trigger('task.acls.cansee::run', $args);
				} else {
					// ACLs plugin not loaded; every files / folders can be
					// accessed
					$args[0]['return'] = 1;
					$bReturn = true;
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
					//$dirs [] = utf8_decode($entry['name']);
					$dirs [] = $entry['name'];
				}
			} // if ($entry['type']=='folder')
		} // foreach

		// The current folder has been processed, are
		// there subfolders in it ?
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
	* @param string $directory	Directory path
	*/
	private static function directoryToArray($directory)
	{
		static $root = '';

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();

		if ($root === '') {
			$aeSettings = \MarkNotes\Settings::getInstance();
			$root = str_replace('/', DS, $aeSettings->getFolderDocs(true));
		}

		$arr = array();

		if ($aeFolders->exists($directory)) {
			// Get the list of files/folders under $directory
			// Only the folder, not subfolders
			$items = $aeFolders->getContent($directory);

			foreach ($items as $item) {
				// Don't take files/folders starting with a dot
				if (substr($item['basename'], 0, 1) !== '.') {
					// Absolute filename / foldername
					$name = rtrim($directory, DS).DS.$item['basename'];
					if ($item['type']=='dir') {
						// It's a folder
						$arr[] = array('name' => $name,'type' => 'folder');
						//$arr[] = array('name' => iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($name)),'type' => 'folder');
					} else {
						// it's a file, get it only if the
						// extension is .md
						if (isset($item['extension'])) {
							if ($item['extension']==='md') {
								$arr[] = array(
									'name' => rtrim($directory,DS).'/'.$item['filename'],
									'type' => 'file');
							}
						}
					} // if ($aeFolders->exists($directory.DS.$file))
				} // if (substr($file, 0, 1) !== '.')
			} // foreach
		} // if ($aeFiles->folderExists($directory))

		$name = array();

		// Sort the array by name
		foreach ($arr as $key => $row) {
			$name[$key] = $row['name'];
		} // foreach

		array_multisort($name, SORT_ASC | SORT_NATURAL | SORT_FLAG_CASE, $arr);

		return $arr;
	}

	/**
	 * The webmaster has disabled to right to see the interface
	 * so, it seems coherent to also disable the listfiles task.
	 */
	private static function notAllowed() : array
	{
		$return = array();

		$return['count'] = 0;
		$return['status'] = 0;
		$return['message'] = 'The administrator has disabled '.
			'access to the interface.';
		return $return;
	}

	private static function doGetList() : array
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Call the ACLs plugin
		$aeEvents->loadPlugins('task.acls.load');

		$args=array();

		$aeEvents->trigger('task.acls.load::run', $args);

		// $bACLsLoaded will be set true if at least one
		// folder is protected
		static::$bACLsLoaded = boolval($aeSession->get('acls', '') != '');

		$sReturn = '';

		/*$bOptimize=false;

		if (!static::$bACLsLoaded) {
			$arrOptimize = $aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);

			// Get the Server_Session parameter i.e. if the
			// list of folder should be retrieved and stored
			// in a $_SESSION variable
			$bOptimize = $arrOptimize['server_session'] ?? false;
			if ($bOptimize) {
				// If the ?reload parameter is on the
				// QUERY_STRING don't use retrieve from
				// $_SESSION. This is the case when the user
				// has added/delete a file/folder from the
				// treeview (right-click -> add folder f.i.)
				$bReload = isset($_GET['reload'])??false;
				if (!$bReload) {
					$sReturn = trim($aeSession->get('treeview_json', ''));
				}
			}
		} // if (static::!$bACLsLoaded)*/

		if ($sReturn === '') {
			$docs = $aeSettings->getFolderDocs(true);

			$aeEvents->loadPlugins('task.acls.cansee');

			// Populate the tree that will be used for jsTree
			// (see https://www.jstree.com/docs/json/)
			list($arr, $count) = self::makeJSON(str_replace('/', DS, $docs));

			// Build the json
			$return = array();
			//$return['settings'] = array('root' => str_replace(DS, '/',
			// $docs));
			$return['from_cache'] = 0;
			$return['count'] = $count;
			$return['tree'] = $arr;
/*
			$aeJSON = \MarkNotes\JSON::getInstance();

			$sReturn = $aeJSON->json_encode($return);

			if (!static::$bACLsLoaded) {
				if ($bOptimize) {
					// Remember for the next call
					$aeSession->set('treeview_json', $sReturn);
				}
			} // if (!static::$bACLsLoaded)*/
		} // if ($sReturn === '')

		return $return;
	}

	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arr = null;

		// Does the interface can be visible ?
		$arrSettings = $aeSettings->getPlugins('/interface');
		$canSee = boolval($arrSettings['can_see'] ?? 1);

		if (!$canSee) {
			$sReturn = json_encode(self::notAllowed());
		} else {

			$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
			$bCache = boolval($arrSettings['enabled'] ?? false);

			if ($bCache) {
				// The list of files can vary from one user to an
				// another so we need to use his username
				$key = $aeSession->getUser().'###listfiles.json';

				$aeCache = \MarkNotes\Cache::getInstance();
				$cached = $aeCache->getItem(md5($key));
				$arr = $cached->get();
			}

			if (is_null($arr)) {
				// Not yet in the cache, retrieve the list of files
				$arr = self::doGetList();

				if ($bCache) {
					// Save the list in the cache
					$arr['from_cache'] = 1;
					$duration = $arrSettings['duration']['default'];
					$cached->set($arr)->expiresAfter($duration);
					$aeCache->save($cached);
					$arr['from_cache'] = 0;
				}
			} else {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug->log('	Retrieving from the cache', 'debug');
				}
				/*<!-- endbuild -->*/
			}
		} // if (!$canSee)

		$aeJSON = \MarkNotes\JSON::getInstance();

		/*<!-- build:debug -->*/
		$aeJSON->debug($aeSettings->getDebugMode());
		/*<!-- endbuild -->*/

		$sReturn = $aeJSON->json_encode($arr);

		header('Content-Type: application/json; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		die($sReturn);
	}
}
