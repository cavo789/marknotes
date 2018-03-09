<?php
/**
* Get the list of .md files present in the /docs folder.
* This plugin will make sure, thanks to ACLs plugin, that
* files are accessible to the visitor
*
* This task won't return a visible output (no json, no html, ...)
* but will initialize an array in his run() function.
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
	 */
	private static function doGetList() : array
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
						$arrFiles[] = $file;
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

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('Get the list of files for the treeview', 'debug');
		}
		/*<!-- endbuild -->*/

		$arr = null;

		$arrSettings = $aeSettings->getPlugins('/interface');

		$canSee = boolval($arrSettings['can_see'] ?? 1);

		if ($canSee) {
			// Call the ACLs plugin
			$aeEvents = \MarkNotes\Events::getInstance();

			$aeEvents->loadPlugins('task.acls.load');
			$args=array();
			$aeEvents->trigger('task.acls.load::run', $args);
			$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);

			$bCache = $arrSettings['enabled'] ?? false;

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
				$arr['files'] = self::doGetList();

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
		} // if ($can_see)

		// Return the array with files accessible to the current user
		$params = $arr['files'];

		// This task has no visible output
		return true;
	}
}
