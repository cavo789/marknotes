<?php
/**
 * Favorites
 *
 * Answer to URL like index.php?task=task.favorites.show
 */
namespace MarkNotes\Plugins\Task\Favorites;

defined('_MARKNOTES') or die('No direct access allowed');

class Show extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.favorites';
	protected static $json_options = 'plugins.options.task.favorites';

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrOptions = self::getOptions('list', array());
		// Sort the array
		sort ($arrOptions);

		$arr = array();

		$arr['title'] = $aeSettings->getText('favorites_title', 'Your favorites notes');

		foreach ($arrOptions as $file) {
			$file = $aeFiles->removeExtension($file);
			$file = str_replace('/', DS, $file);
			$arr['files'][] = array(
				'file'=>$file,
				'id'=>md5($file)
			);
		}

		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: application/json');
		echo json_encode($arr, JSON_PRETTY_PRINT);
		die();
	}
}
