<?php
/**
 * Add a Login button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Login extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.login';
	protected static $json_linked = '';

	public static function add(&$buttons = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// fnPluginTaskLogin is a function defined in the file
		// /plugins/task/login/assets/login.js
		// data-task="fnPluginTaskLogin" means that marknotes.js
		// will call that function

		$type = ($aeSession->get('authenticated', 0) == 0 ? 'in' : 'out');

		$buttons['app'][] = self::button(
			array(
				'name' => 'login',
				'title' => 'loginform_'.$type,
				'default' => 'Log '.$type,
				'task' => 'fnPluginTaskLog'.$type,
				'id' => 'type_login',
				'icon' => 'sign-'.$type
			)
		);

		return true;
	}

	/**
	 * No need to add the button if there is no credentials
	 * When the webmaster has set login='' and password='' in the settings.json file
	 */
	protected static function canAdd() : bool
	{
		if ($bReturn = parent::canAdd()) {
			// We can continue
			$bReturn = false;

			$aeSettings = \MarkNotes\Settings::getInstance();
			$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_LOGIN);

			$login = trim($arrSettings['username'] ?? '');
			$password = trim($arrSettings['password'] ?? '');

			// If both login and password are empty (will probably be the
			// case on a localhost server), there is no need to add
			// the Login button
			if (($login !== '') && ($password !== '')) {
				$bReturn = true;
			/*<!-- build:debug -->*/
			} else {
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("The login and/or the password is empty, ".
						"the login form is therefore disabled", "warning");
				}
			/*<!-- endbuild -->*/
			}
		} // if ($bReturn = parent::canAdd())

		return $bReturn;
	}
}
