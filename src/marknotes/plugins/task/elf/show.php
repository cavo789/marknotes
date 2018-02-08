<?php
/**
 * ELF - File manager
 * Show the ELF interface in a iframe
 *
 * Answer to URL like index.php?task=task.elf.show
 *
 * @link https://github.com/Studio-42/elFinder
 */

namespace MarkNotes\Plugins\Task\Elf;

defined('_MARKNOTES') or die('No direct access allowed');

class Show extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.filemanager';
	protected static $json_options = 'plugins.options.task.filemanager';

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arr = array();

		$arr['title'] = $aeSettings->getText('file_manager');

		if (boolval($aeSession->get('authenticated', 0))) {

			$app = $aeSettings->getFolderAppRoot();

			// ELF is a file-manager and the interface is based
			// on a file called "elfinder.html".
			// Build the URL to the file so we can then use it
			// in a iframe
			$dir = dirname(str_replace($app, '', __FILE__));
			$dir = rtrim(str_replace(DS, '/', $dir), DS).'/';
			$dir .= 'libs/';
			$url = $aeFunctions->getCurrentURL().$dir;

			$arr['html'] =
				'<iframe id="FileManager" '.
					'src="'.$url.'elfinder.html" '.
					'width="500" height="600" frameBorder="0">'.
					'<p>Your browser does not support iframes.</p>'.
				'</iframe>';
		} else {

			// The user isn't logged in, he can't modify settings
			$aeSettings = \MarkNotes\Settings::getInstance();
			$arr['html'] = '<p class="text-danger">'.
				$aeSettings->getText('not_authenticated').'</p>';
		}

		header('Content-Type: application/json');
		echo json_encode($arr, JSON_PRETTY_PRINT);

		return true;
	}

}
