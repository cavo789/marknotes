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
		$aeSession = \MarkNotes\Session::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Retrieve the submitted login and password (by submitting the login form)
		$login = $aeFunctions->getParam('username', 'string', '', true);
		$login = json_decode(urldecode($login));

		$password = $aeFunctions->getParam('password', 'string', '', true, 40);
		$password = json_decode(urldecode($password));

		$status = 0;

		// Get the username / password from settings.json
		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_LOGIN);

		// Should the login plugin be active ?
		$bEnabled = boolval($arrSettings['enabled'] ?? 0);

		if ($bEnabled) {
			// Yes
			$bLogin = ($login === $arrSettings['username']);
			$bPassword = ($password === $arrSettings['password']);

			// OK only if a strict equality
			$status = ($bLogin && $bPassword) ? 1 : 0;
		} else {
			// The login plugin isn't active => authenticate by default
			$status = 1;
		}

		$aeSession->set('authenticated', $status);

		header('Content-Type: application/json');

		// Should be 1 / 0, not a boolean (true/false)
		echo json_encode(array('status' => ($status?1:0)));

		return true;
	}
}
