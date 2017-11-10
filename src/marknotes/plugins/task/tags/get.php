<?php
/**
 * Return a JSON with the list of known tags i.e.
 *
 *    - coming from the settings.json -> plugins -> options -> page -> tags
 *    - every foldername under the /docs folder will be considered as tag
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
	 * Retrieve the list of folders but only when the user has access
	 * to it. Rely on the ACLs plugin for this purpose.
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
		$arrFiles = $args[0];

		// Now, only keep directory name, not files
		$arrFiles = array_map("dirname", $arrFiles);

		// And make the array unique (not the same folder twice)
		$arrFolders = $aeFunctions->array_iunique($arrFiles, SORT_STRING);

		// The first position of the array is the /docs folder, remove it
		unset($arrFolders[0]);

		// Now, just keep relative name (like "folder_name") and not fullname
		// (like c:\website\marknotes\docs\folder_name
		$arrFolders = array_map("basename", $arrFolders);

		return $arrFolders;
	}

	public static function run(&$params = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		// get the list of folders and generate a "tags" node
		$sReturn = '';

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);
		$bOptimize = boolval($arrSettings['server_session'] ?? false);

		if ($bOptimize) {
			// Get the list of files/folders from the session object if possible
			// If found, it's a JSON object
			$aeSession = \MarkNotes\Session::getInstance();
			$sReturn = $aeSession->get('Tags', '');
		}

		if ($sReturn == '') {
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
				$arrTags[] = array('name' => $value);
			}

			// Get tags.json if the file exists
			$fname = $aeSettings->getFolderWebRoot().'tags.json';
			if (is_file($fname)) {
				$content = json_decode(file_get_contents($fname), true);
				foreach ($content as $tag) {
					$arrTags[] = array('name'=>$tag);
				}
			}

			// Be carefull, folders / filenames perhaps contains accentuated
			// characters
			// $arrTags = array_map('utf8_encode', $arrTags);

			$aeJSON = \MarkNotes\JSON::getInstance();
			$sReturn = $aeJSON->json_encode($arrTags);

			if ($bOptimize) {
				// Remember for the next call
				$aeSession->set('Tags', $sReturn);
			}
		} // if (count($arrTags)==0)

		header('Content-Type: application/json; charset=UTF-8');
		header("cache-control: must-revalidate");
		$offset = 48 * 60 * 60;  // 48 hours
		$expire = "expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
		header($expire);

		echo $sReturn;

		return true;
	}
}
