<?php
/**
 * Favorites - Get the icon i.e. determine if the note is already
 * mentionned in the list of favorites or no, not yet
 *
 * Answer to URL like index.php?task=task.favorites.geticon
 *
 * Return a JSON like
 *
 *		{
 *			"title": "Remove this note from yours favorites",
 *			"icon": "star",
 *			"task": "remove"
 *		}
 */
namespace MarkNotes\Plugins\Task\Favorites;

defined('_MARKNOTES') or die('No direct access allowed');

class GetIcon extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.favorites';
	protected static $json_options = 'plugins.options.task.favorites';

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the filename to check (f.i. marknotes/Ideas)
		$filename = json_decode(urldecode($aeFunctions->getParam('param', 'string', '', true)));
		$filename = $aeFiles->removeExtension($filename);

		// Get the list of favorites from the settings.json file
		$arrOptions = self::getOptions('list', array());

		$bFound = false;
		foreach ($arrOptions as $fav) {
			// Loop and check if the file in mentionned
			if ($fav == $filename) {
				// Yes : the file is already mentionned in the
				// favotites
				$bFound = true;
				break;
			}
		}

		$arr = array();

		// Prepare the JSON
		if ($bFound) {
			$arr['title'] = $aeSettings->getText('favorites_removefrom', 'Remove this note from yours favorites');
			$arr['icon'] = 'star';
			$arr['task'] = 'remove';
		} else {
			$arr['title'] = $aeSettings->getText('favorites_addto', 'Add this note into yours favorites');
			$arr['icon'] = 'star-o';
			$arr['task'] = 'add';
		}

		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: application/json');
		echo json_encode($arr, JSON_PRETTY_PRINT);
		die();
	}
}
