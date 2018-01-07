<?php
/**
 * Get interface buttons - Return the HTML with all enabled buttons
 */
namespace MarkNotes\Plugins\Task\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class GetButtons extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.buttons';
	protected static $json_options = '';

	private static function doGetButtons() : array
	{
		$aeEvents = \MarkNotes\Events::getInstance();

		// Call plugins that are responsible to add icons to the toolbar
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('buttons');
		$buttons = array();
		$args = array(&$buttons);
		$aeEvents->trigger('buttons::add.buttons', $args);

		return $args[0];
	}

	/**
	 * Return the code for showing the login form and respond to the login action
	 */
	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('Get list of buttons', 'debug');
		}
		/*<!-- endbuild -->*/

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = boolval($arrSettings['enabled'] ?? false);

		if ($bCache) {
			// Get the list of buttons.

			// The list of files can vary from one user to an
			// another so we need to use his username
			$loggedIn = $aeSession->get('authenticated')?'IN':'OUT';

			// Keep one version of the list of buttons for
			//	* each Apached logged in users (htpasswd protection)
			//	* one when the user is logged in / one when logged out
			$key = $aeSession->getUser().'###'.$loggedIn.
				'###'.'buttons.json';

			$aeCache = \MarkNotes\Cache::getInstance();
			$cached = $aeCache->getItem(md5('buttons.json'));
			$arr = $cached->get();
		}

		if (is_null($arr)) {
			$arr['buttons'] = self::doGetButtons();
			if ($bCache) {
				// Save the list in the cache
				$arr['from_cache'] = 1;
				$duration = $arrSettings['duration']['default'];
				$cached->set($arr)->expiresAfter($duration)->addTag(md5('interface'));
				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}
		} else {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('   Retrieving from the cache', 'debug');
			}
			/*<!-- endbuild -->*/
		} // if (is_null($arr))

		header('Content-Type: application/json');
		echo json_encode(array('status'=>1,'buttons'=>$arr['buttons']));

		return true;
	}
}
