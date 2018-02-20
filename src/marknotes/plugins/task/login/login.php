<?php
/**
 * Process logins
 */
namespace MarkNotes\Plugins\Task\Login;

defined('_MARKNOTES') or die('No direct access allowed');

class Login extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.login';
	protected static $json_options = JSON_OPTIONS_LOGIN;

	/**
	 * Check credentials against login/password from settings.json
	 * and authenticate the user is there is a perfect match
	 */
	public static function run(&$params = null) : bool
	{
		$status = 0;

		if (self::isEnabled(true)) {
			// Ok, the login task is enabled

			$aeSettings = \MarkNotes\Settings::getInstance();

			// Get the username / password from settings.json
			$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_LOGIN);

			$aeFunctions = \MarkNotes\Functions::getInstance();

			// Retrieve the submitted login and password
			// (by submitting the login form)
			$login = $aeFunctions->getParam('username', 'string', '', true);
			$login = json_decode(urldecode($login));

			$password = $aeFunctions->getParam('password', 'string', '', true, 40);
			$password = json_decode(urldecode($password));

			// Get the username / password from settings.json
			$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_LOGIN);

			$bLogin = ($login === $arrSettings['username']);
			$bPassword = ($password === $arrSettings['password']);

			// OK only if a strict equality
			$status = ($bLogin && $bPassword) ? 1 : 0;

		} else {

			// The login task isn't enabled
			$status = 0;

		}

		$aeSession = \MarkNotes\Session::getInstance();
		$aeSession->set('authenticated', $status);

		header('Content-Type: application/json');

		// Should be 1 / 0, not a boolean (true/false)
		echo json_encode(array('status' => ($status?1:0)));

		return true;
	}
}
