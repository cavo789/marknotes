<?php
/**
 * Login - Return the HTML of the login form
 */
namespace MarkNotes\Plugins\Task\Login;

defined('_MARKNOTES') or die('No direct access allowed');

class GetForm extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.login';
	protected static $json_options = JSON_OPTIONS_LOGIN;

	/**
	 * Return the code for showing the login form and respond to the login action
	 */
	public static function run(&$params = null) : bool
	{

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$filename = __DIR__.'/assets/login.frm';

		if ($aeFiles->FileExists($filename)) {
			// Get the root URL
			$root = rtrim($aeFunctions->getCurrentURL(), '/');

			$form = file_get_contents($filename);

			$form = str_replace('%ROOT%', rtrim($aeFunctions->getCurrentURL(), '/'), $form);

			$form = str_replace('%LOGINFORM%', $aeSettings->getText('loginform', 'Login form'), $form);
			$form = str_replace('%LOGIN%', $aeSettings->getText('login', 'Username'), $form);
			$form = str_replace('%PASSWORD%', $aeSettings->getText('password', 'Password'), $form);
			$form = str_replace('%SIGNIN%', $aeSettings->getText('signin', 'Sign in'), $form);

		/*<!-- build:debug -->*/
		} else { // if ($aeFiles->FileExists($filename))
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("The file [".$filename."] is missing", "error");
			}
		/*<!-- endbuild -->*/
		} // if ($aeFiles->FileExists($filename))

		header('Content-Type: application/json');
		echo json_encode(array('form'=>$form));

		return true;
	}
}
