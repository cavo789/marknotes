<?php
/**
* Get the list of folders under /docs
*
* Can answer to /index.php?task=task.listfiles.getfolders
*
* The list can be restricted to a folder like
*	/index.php?task=task.listfiles.getfolders&restrict_folder=PHP
* which limit the list to folders under /docs/PHP
*/

namespace MarkNotes\Plugins\Task\ListFiles;

defined('_MARKNOTES') or die('No direct access allowed');

class GetFolders extends \MarkNotes\Plugins\Task\Plugin
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
			$aeDebug->log('Get list of files in [' . $docs . ']', 'debug');
		}
		/*<!-- endbuild -->*/

		$ext = '*.md';
		$arr = [];

		$aeFolders = \MarkNotes\Folders::getInstance();

		// If $subfolder isn't empty, restrict the list to that
		// subfolder and not all files/folders under /docs
		if ($subfolder !== '') {
			$docs = rtrim($docs, DS) . DS . $subfolder;
		}

		$arr = $aeFolders->getContent($docs, true);

		$arrFiles = [];

		if (count($arr) > 0) {
			// Only the name of the folder, not the fullpath
			$docs = $aeSettings->getFolderDocs(false);

			foreach ($arr as $item) {
				$type = $item['type'] ?? '';

				if ($type == 'file') {
					$extension = $item['extension'] ?? '';

					if ($extension == 'md') {
						$file = str_replace('/', DS, $item['path']);
						// Be sure the filename starts with
						// docs/
						if (!$aeFunctions->startsWith($file, $docs)) {
							$file = $docs . $file;
						}

						// Instead of using a unusefull index
						// use the file timestamp (last mod date/time)
						$dte = $aeFiles->timestamp($aeFiles->makeFileNameAbsolute($file));

						$arrFiles[$dte . '_' . md5($file)] = $file;
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
			$args = [&$arrFiles];
			$aeEvents->trigger('task.acls.filter_list::run', $args);

			// Retrieve the filtered array i.e. that Files
			// well accessible to the current user
			$arrFiles = $args[0];
		} // if ($bACLsLoaded)

		return $arrFiles;
	}

	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// Restrict folder will allow to limit the search to a given
		// subfolder and not search for everyting under /docs
		$restrict_folder = trim($aeFunctions->getParam('restrict_folder', 'string', '', false, SEARCH_MAX_LENGTH));

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

			$aeEvents = \MarkNotes\Events::getInstance();

			$aeEvents->loadPlugins('task.acls.load');
			$args = [];
			$aeEvents->trigger('task.acls.load::run', $args);
			$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);

			$bCache = $arrSettings['enabled'] ?? false;

			if ($bCache) {
				$aeCache = \MarkNotes\Cache::getInstance();

				// The list of files can vary from one user to an
				// another so we need to use his username
				$key = $aeSession->getUser() . '###listfiles';
				$cached = $aeCache->getItem(md5($key));
				$arr = $cached->get();

				if ($arr['files'] == []) {
					$arr = null;
				}
			}

			if (is_null($arr)) {
				// $restrict_folder can be empty (search everything
				// under /docs) or a subfolder (like 'public' for
				// searching only under /docs/public)
				$arr['files'] = self::doGetList($restrict_folder);

				if ($bCache) {
					// Save the list in the cache
					$duration = $arrSettings['duration']['default'];
					$cached->set($arr)->expiresAfter($duration)->addTag(md5('listfiles'));
					$aeCache->save($cached);
					$arr['from_cache'] = 0;
				}
			} else {
				$arr['from_cache'] = 1;
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug->log('	Retrieving from the cache', 'debug');
				}
				/*<!-- endbuild -->*/
			} // if (is_null($arr))
		} // if ($showLogin)

		// Now extract only folder name, remove duplicates
		// and sort
		$arrFiles = $arr['files'];
		$arrFiles = array_map('dirname', $arrFiles);
		$arrFiles = array_unique($arrFiles);
		sort($arrFiles);

		header('Content-Type: application/json');
		echo json_encode($arrFiles, JSON_PRETTY_PRINT);

		// This task has no visible output
		return true;
	}
}
