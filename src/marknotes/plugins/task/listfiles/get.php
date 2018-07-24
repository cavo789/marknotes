<?php
/**
* Get the list of .md files present in the /docs folder.
* This plugin will make sure, thanks to ACLs plugin, that
* files are accessible to the visitor
*
* This task won't return a visible output (no json, no html, ...)
* but will initialize an array in his run() function.
*
* The function will return an array with a key and a filename.
* The key is not just a incremental figure (1, 2, 3, ...), unusefull,
* but the timestamp of the file AND THE MD5, for instance :
*
* 	Key syntax = $timestamp.'_'.md5($file)
*
*		[1510574854_4bc1946d9f928e4f87deb4d7a8c8aad9] => docs/folder/file.md
*		[1510574854_fcd38c89c9a5d9a00422fcd3121a5db8] => docs/folder/file999.md
*		[1517479015_ce02c0df9aafe987154c15371c573dad] => docs/folder2/file2..md
*
* MD5 is indeed needed because only the timestamp is not enough
* because two or more files can have exactly the same timestamp.
* So, in order to not "loose" files in the array (same timestamp),
* we need to be sure to make the key unique.
*
* So we can make a revert sort on the array (with krsort()) and
* quickly retrieve the last added/modified notes f.i. (see plugin
* task.lastmodified.getlist)
*
* Example of a call :
*
*		$arrFiles = array();
*		// Call the listfiles.get event and initialize $arrFiles
*		$aeEvents = \MarkNotes\Events::getInstance();
*		$args=array(&$arrFiles);
*		$aeEvents->loadPlugins('task.listfiles.get');
*		$aeEvents->trigger('task.listfiles.get::run', $args);
*
*		$arrFiles = $args[0]['files'];
*
*		foreach ($arrFiles as $file) {
*			echo "Dear visitor, the file ".$file." is accessible to ".
*				"you</br>";
*		}
*
* Can answer to /index.php?task=task.listfiles.get
* (but there is no output)
*/
namespace MarkNotes\Plugins\Task\ListFiles;

defined('_MARKNOTES') or die('No direct access allowed');

class Get extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.listfiles';
	protected static $json_options = 'plugins.options.task.listfiles';

	/**
	 * Get the list of files
	 *
	 *	$subfolder : limit the list to a given subfolder
	 *			f.i. "public" will restrict the list to only
	 *			/docs/public/
	 */
	private static function doGetList(string $subfolder = '') : array
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$docs = $aeSettings->getFolderDocs(true);

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('Get list of files in ['.$docs.']', 'debug');
		}
		/*<!-- endbuild -->*/

		$ext = '*.md';
		$arr = array();

		$aeFolders = \MarkNotes\Folders::getInstance();

		// If $subfolder isn't empty, restrict the list to that
		// subfolder and not all files/folders under /docs

		if ($subfolder!=='') {
			$docs = $aeFiles->makeFileNameAbsolute($subfolder);
		}

		$arr = $aeFolders->getContent($docs, true);

		$arrFiles = array();

		if (count($arr)>0) {
			// Only the name of the folder, not the fullpath
			$docs = $aeSettings->getFolderDocs(false);

			foreach ($arr as $item) {
				$type = $item['type']??'';

				if ($type=='file') {
					$extension = $item['extension']??'';

					if ($extension == 'md') {
						$file = str_replace('/', DS, $item['path']);
						// Be sure the filename starts with
						// docs/
						if (!$aeFunctions->startsWith($file, $docs)) {
							$file = $docs.$file;
						}

						// Instead of using a unusefull index
						// use the file timestamp (last mod date/time)
						$dte = $aeFiles->timestamp($aeFiles->makeFileNameAbsolute($file));

						$arrFiles[$dte.'_'.md5($file)] = $file;
					}
				}
			}
		}

		// $bACLsLoaded will be set true if at least
		// one folder is protected
		$bACLsLoaded = boolval($aeSession->get('acls', '') != '');

		if ($bACLsLoaded) {
			// Run the filter_list task to remove any protected files
			// not allowed for the current user
			$aeEvents->loadPlugins('task.acls.filter_list');
			$args=array(&$arrFiles);
			$aeEvents->trigger('task.acls.filter_list::run', $args);

			// Retrieve the filtered array i.e. that Files
			// well accessible to the current user
			$arrFiles=$args[0];

		} // if ($bACLsLoaded)
		return $arrFiles;
	}

	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// $params can be an array with a params entry.
		// Like in the plugins/task/search/search.php where
		// restrict_folder is set so we can restrict the search to
		// a subfolder and not the full /docs folder
		$restrict_folder = $params['params']['restrict_folder']??'';

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('Get the list of files for the treeview', 'debug');
		}
		/*<!-- endbuild -->*/

		$arr = null;

		$arrSettings = $aeSettings->getPlugins('/interface');
		$showLogin = boolval($arrSettings['show_login'] ?? 1);
		$aeSession = \MarkNotes\Session::getInstance();
		$bAuthenticated = boolval($aeSession->get('authenticated', 0));

		if ($showLogin && !$bAuthenticated) {
			// The site owner wish to show the login screen before
			// showing the interface and the user is not authenticated
			// Don't return any files since the user should be
			// logged in
			$arr = null;
		} else {

			// Call the ACLs plugin
			$aeEvents = \MarkNotes\Events::getInstance();

			$aeEvents->loadPlugins('task.acls.load');
			$args=array();
			$aeEvents->trigger('task.acls.load::run', $args);
			$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);

			// Don't use the cache when the list of files is restricted
			// to a specific folder
			if ($restrict_folder !== "") {
				$bCache = false;
			} else {
				$bCache = $arrSettings['enabled'] ?? false;
			}

			if ($bCache) {
				$aeCache = \MarkNotes\Cache::getInstance();

				// The list of files can vary from one user to an
				// another so we need to use his username
				$key = $aeSession->getUser().'###listfiles';
				$cached = $aeCache->getItem(md5($key));
				$arr = $cached->get();

				if ($arr['files']==array()) {
					$arr=null;
				}
			}

			if (is_null($arr)) {
				// $restrict_folder can be empty (search everything
				// under /docs) or a subfolder (like 'public' for
				// searching only under /docs/public)
				$arr['files'] = self::doGetList($restrict_folder);

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
			} // if (is_null($arr))
		} // if ($showLogin)

		// Return the array with files accessible to the current user
		$params = $arr['files'];

		// This task has no visible output
		return true;
	}
}
