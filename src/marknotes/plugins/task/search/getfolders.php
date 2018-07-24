<?php
/**
 * GetFolders - Return the HTML of the restrict_folder form
 */
namespace MarkNotes\Plugins\Task\Search;

defined('_MARKNOTES') or die('No direct access allowed');

class Getfolders extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.search';
	protected static $json_options = 'plugins.options.task.search';

	private static function restrictFistFolderLevel(array $arrFolders) : array
	{
		$arr = null;

		$aeSettings = \MarkNotes\Settings::getInstance();

		// Check if the cache is enable
		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;

		if ($bCache) {
			$aeCache = \MarkNotes\Cache::getInstance();
			$aeSession = \MarkNotes\Session::getInstance();

			// The list of files can vary from one user to an
			// another so we need to use his username
			$key = $aeSession->getUser().'###search_getfolders';

			$cached = $aeCache->getItem(md5($key));
			$arr = $cached->get();

			if ($arr['folders']==array()) {
				$arr=null;
			}
		}

		if (is_null($arr)) {

			$aeFunctions = \MarkNotes\Functions::getInstance();

			// Get the docs folders (like "docs/")
			$doc = $aeSettings->getFolderDocs(false);

			$arrRootFolders=array();
			$arrRootFolders[] = '.';

			foreach ($arrFolders  as $tmp => $value) {

				if ($aeFunctions->startsWith($value, $doc)) {
					$value = substr($value, strlen($doc));
				}

				$value = str_replace(DS, '/', $value);

				preg_match("/([^\/]*)/mi", $value, $matches);

				$firstFolderLevel = $matches[0];

				if (!in_array($firstFolderLevel, $arrRootFolders)) {
					$arrRootFolders[] = $firstFolderLevel;
				}
			}

			// Sort the list
			natcasesort($arrRootFolders);

			$arr['folders'] = $arrRootFolders;

			if ($bCache) {
				// Save the list in the cache
				$duration = $arrSettings['duration']['default'];
				$cached->set($arr)->expiresAfter($duration);
				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}

		} else {

			$arr['from_cache'] = 1;

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('	Retrieving from the cache', 'debug');
			}
			/*<!-- endbuild -->*/
		} // if (is_null($arr))

		$arrRootFolders = $arr['folders'];

		return $arrRootFolders;

	}
	/**
	 * Return the code for showing the login form and respond
	 * to the login action
	 */
	public static function run(&$params = null) : bool
	{
		$form = '';

		if (self::isEnabled(true)) {
			// Ok, the login task is enabled
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$filename = __DIR__.'/assets/getfolders.frm';

			if ($aeFiles->exists($filename)) {
				// Get the root URL
				$root = rtrim($aeFunctions->getCurrentURL(), '/');

				$form = $aeFiles->getContent($filename);

				$form = str_replace('%ROOT%', rtrim($aeFunctions->getCurrentURL(), '/'), $form);
				$form = str_replace('%SEARCH_APPLY%', $aeSettings->getText('search_apply_folder', ''), $form);
				$form = str_replace('%SEARCH_DEFINE_TITLE%', $aeSettings->getText('search_define_folder_title', ''), $form);
				$form = str_replace('%SEARCH_REMOVE%', $aeSettings->getText('search_remove_folder', ''), $form);
				$form = str_replace('%SEARCHED_ADVANCED_FORM%', $aeSettings->getText('search_advanded_form', ''), $form);

				// Now, build the list of folders
				$arrFiles = array();
				$args=array(&$arrFiles);
				$aeEvents->loadPlugins('task.listfiles.get');
				$aeEvents->trigger('task.listfiles.get::run', $args);

				// $arrFiles contains the list of files => extract
				// folder names, make unique and sort the list
				$arrFolders = array_map('dirname', $arrFiles);

				$arrSettings = $aeSettings->getPlugins(self::$json_options);
				$restrict_folders_first_level = $arrSettings['restrict_folders_first_level']??1;

				if ($restrict_folders_first_level) {
					// Keep only the root level (folders immediatly
					// under /docs) and not all folders (as deep they are)
					$arrFolders = self::restrictFistFolderLevel($arrFolders);
				}

				$arrFolders = array_unique($arrFolders);

				// Build the html select
				$values = '';
				foreach ($arrFolders  as $tmp => $value) {
					$values .= '<option value="'.$value.'">'.$value.'</option>';
				}

				$form = str_replace('%FOLDERS%', $values, $form);

			/*<!-- build:debug -->*/
			} else { // if ($aeFiles->exists($filename))
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("The file [".$filename."] is missing", "error");
				}
			/*<!-- endbuild -->*/
			} // if ($aeFiles->exists($filename))
		}

		header('Content-Type: application/json');
		echo json_encode(array('form'=>$form));

		return true;
	}
}
