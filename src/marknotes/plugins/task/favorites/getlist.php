<?php
/**
 * Favorites - Retrieve the list of favorites; return a JSON string
 *
 * Answer to URL like index.php?task=task.favorites.getlist
 */

namespace MarkNotes\Plugins\Task\Favorites;

defined('_MARKNOTES') or die('No direct access allowed');

class GetList extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.favorites';
	protected static $json_options = 'plugins.options.task.favorites';

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arr = array();

		// 1. Be sure the task is well enabled
		$arrSettings = $aeSettings->getPlugins(self::$json_settings, array('enabled'=>0));
		$bContinue = boolval($arrSettings['enabled']??0);

		if ($bContinue) {

			// Get the list of favorites from the settings.json file
			$arrOptions = self::getOptions('list', array());

			// Sort the array
			sort($arrOptions);

			$arr['title'] = $aeSettings->getText('favorites_title', 'Your favorites notes');

			// Get the docs folder
			$docs = $aeSettings->getFolderDocs(false);

			// $arrOptions, if not empty, is a list of filenames like
			//
			//		christophe/note
			//		marknotes/Ideas
			//		homepage
			//
			// the root "docs/" folder is not mentionned and the ".md"
			// extension not needed too.
			foreach ($arrOptions as $file) {
				$file = $aeFiles->removeExtension($file);

				$file = str_replace('/', DS, $file);

				// Make sure the favorites still exists
				$fullname = $aeFiles->makeFileNameAbsolute($file).'.md';

				if ($aeFiles->exists($fullname)) {
					$arr['files'][] = array(
						'file'=>$file,
						'id'=>md5($docs.$file) // md5 needs "docs/"
					);
				}
			}
		}

		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: application/json');

		echo json_encode($arr, JSON_PRETTY_PRINT);

		die();
	}
}
