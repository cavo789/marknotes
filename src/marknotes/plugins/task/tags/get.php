<?php
/**
 * Return a JSON with the list of known tags i.e.
 *
 *    - coming from the settings.json -> plugins.options.page.tags
 *    - every foldername under the /docs folder will be
 *		considered as tag
 *
 * In settings.json, the list of tags is coded like this :
 *
 *		"plugins": {
 *			"options": {
 *				"page": {
 *					"tags": {
 *						"tags": [
 *							"tag_1",
 *							"tag_2",
 *							"tag_3"
 *						]
*					}
*				}
*			}
*		}
 */
namespace MarkNotes\Plugins\Task\Tags;

defined('_MARKNOTES') or die('No direct access allowed');

class Get extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.tags';
	protected static $json_options = 'plugins.options.content.html.tags';

	/**
	 * Retrieve the list of folders but only when the user
	 * has access to it.
	 * Rely on the ACLs plugin for this purpose.
	 */
	private static function getFolders() : array
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();

		// Call the listfiles.get event and initialize $arrFiles
		$aeEvents = \MarkNotes\Events::getInstance();
		$args=array(&$arrFiles);
		$aeEvents->loadPlugins('task.listfiles.get');
		$aeEvents->trigger('task.listfiles.get::run', $args);
		$arrFiles = $args[0]['files'];


		// Now, only keep directory name, not files
		$arrFiles = array_map("dirname", $arrFiles);

		// And make the array unique (not the same folder twice)
		$arrFolders = $aeFunctions->array_iunique($arrFiles, SORT_STRING);

		// The first position of the array is the /docs folder,
		// remove it
		unset($arrFolders[0]);

		// Now, just keep relative name (like "folder_name")
		// and not fullname
		// (like c:\website\marknotes\docs\folder_name
		$arrFolders = array_map("basename", $arrFolders);

		return $arrFolders;
	}

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$arr = null;
		$arrTags = array();

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = boolval($arrSettings['enabled'] ?? false);

		if ($bCache) {
			$aeCache = \MarkNotes\Cache::getInstance();

			// The list of tags can vary from one user to an
			// another so we need to use his username
			$key = $aeSession->getUser().'###tags';
			$cached = $aeCache->getItem(md5($key));
			$arr = $cached->get();
		}

		if (is_null($arr)) {
			// Get the list of folders
			$arr = self::getFolders();

			// And append tags from settings.json
			$arrTags = self::getOptions('keywords', array());

			if (count($arrTags) > 0) {
				foreach ($arrTags as $tag) {
					$arr[] = $tag;
				}
			}

			// natcasesort and array_iuniquemakes an associative array
			// with positions, not needed
			$arrTags = $arr;

			$aeFunctions = \MarkNotes\Functions::getInstance();
			$arr = $aeFunctions->array_iunique($arr, SORT_STRING);
			natcasesort($arr);
			$arrTags = array();
			foreach ($arr as $key => $value) {
				if($value!=='') {
					$arrTags[] = array('name' => $value);
				}
			}

			// Get tags.json if the file exists
			$fname = $aeSettings->getFolderWebRoot().'tags.json';
			if ($aeFiles->exists($fname)) {
				$content = json_decode($aeFiles->getContent($fname), true);
				foreach ($content as $tag) {
					if($tag!=='') {
						$arrTags[] = array('name'=>$tag);
					}
				}
			}

			$arr=array();
			$arr['tags'] = $arrTags;

			if ($bCache) {
				// Save the list in the cache
				$arr['from_cache'] = 1;
				$duration = $arrSettings['duration']['default'];
				$cached->set($arr)->expiresAfter($duration);
				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}
		} else { // if (count($arrTags)==0)
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("    Retrieved from cache","debug");
			}
			/*<!-- endbuild -->*/
		}

		// Be carefull, folders / filenames perhaps contains accentuated
		// characters
		// $arrTags = array_map('utf8_encode', $arrTags);

		$aeJSON = \MarkNotes\JSON::getInstance();
		$sReturn = $aeJSON->json_encode($arr['tags']);

		header('Content-Type: application/json; charset=UTF-8');
		header("cache-control: must-revalidate");
		$offset = 48 * 60 * 60;  // 48 hours
		$expire = "expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
		header($expire);

		echo $sReturn;

		return true;
	}
}
