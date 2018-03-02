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

	private static function doIt() : string
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$app = $aeSettings->getFolderAppRoot();

		// ELF is a file-manager and the interface is based
		// on a file called "elfinder.html".
		// Build the URL to the file so we can then use it
		// in a iframe
		$dir = dirname(str_replace($app, '', __FILE__));
		$dir = rtrim(str_replace(DS, '/', $dir), DS).'/';
		$dir .= 'libs/';
		$url = $aeFunctions->getCurrentURL().$dir;

		return
			'<iframe id="FileManager" '.
				'src="'.$url.'elfinder.html" '.
				'width="500" height="600" frameBorder="0">'.
				'<p>Your browser does not support iframes.</p>'.
			'</iframe>';
	}

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// File manager is a very dangerous function since we'll
		// display a form that will allow to do a lot of things on
		// files / folders.

		$arr = array();
		$arr['title'] = $aeSettings->getText('file_manager');

		// 1. Be sure the task is well enabled
		$arrSettings = $aeSettings->getPlugins(self::$json_settings, array('enabled'=>0));

		$bContinue = boolval($arrSettings['enabled']??0);

		if ($bContinue) {

			// Ok, the file manager task is enabled
			if (boolval($aeSession->get('authenticated', 0))) {
				$arr['html'] = self::doIt();
			} else {
				// The user isn't logged in, he can't modify settings
				$arr['html'] = '<p class="text-danger">'.
					$aeSettings->getText('not_authenticated').'</p>';

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$arr['html'] .= '<p class="text-debug">'.
						'Please first use the login feature.'.
						'</p>';
				}
				/*<!-- endbuild -->*/
			}
		} else {
			// File manager task disabled in settings.json,
			// plugins.task.filemanager.enabled has been set to 0
			$arr['html'] = '<p class="text-danger">'.
				$aeSettings->getText('action_prohibited').'</p>';

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$arr['html'] .= '<p class="text-debug">'.
					'The task filemanager is not enabled in '.
					'plugins.task.filemanager.enabled.'.
					'</p>';
			}
			/*<!-- endbuild -->*/

		}

		//$arrSettings = self::getOptions('enabled', array('enabled'=>0));

		header('Content-Type: application/json');
		echo json_encode($arr, JSON_PRETTY_PRINT);

		return true;
	}

}
