<?php
/**
 * htpasswd - Add, update or remove a user in the .htpasswd file
 *
 * Answer to URL like index.php?task=task.htpasswd.protect&task=xxx&user=xxx&passwd=xxx
 */
namespace MarkNotes\Plugins\Task\htpasswd;

defined('_MARKNOTES') or die('No direct access allowed');

class Protect extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.htpasswd';
	protected static $json_options = 'plugins.options.task.htpasswd';

	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$username = json_decode(urldecode($aeFunctions->getParam('user', 'string', '', true)));

		$password = json_decode(urldecode($aeFunctions->getParam('password', 'string', '', true)));

		$task = 'add';
		$username = 'christophe';
		$password = 'test';

		$dir = rtrim(__DIR__, DS).DS;
		$lib = $dir.'libs/ozanhazer/Htpasswd.php';

		if (is_file($lib)) {

			require_once($lib);

			require_once($dir.'helper/htpasswd.php');

			$aeSettings = \MarkNotes\Settings::getInstance();

			// The .htpasswd file should be stored in the root
			// folder of the website
			$root = $aeSettings->getFolderWebRoot();
			$htpasswdFile = rtrim($root,DS).DS.'.htpasswd';

			// and get filename for the .htaccess file
			$htaccessFile = rtrim($root,DS).DS.'.htaccess';

			$aeHTPasswd = new \MarkNotes\Plugins\Task\htpasswd\helper\Manage($htaccessFile, $htpasswdFile);

			$return = $aeHTPasswd->add($username, $password);

			$arr = array();

			$msg = $aeSettings->getText('htpasswd_added',
				'The user [$1] has been added, he can now use the application');
			$msg = str_replace('$1', $username, $msg);

			$arr['message'] = $msg;
			$arr['status'] = '1';

		} else {

			$msg = $aeSettings->getText('error_lib_not_found', 'Error - The library [$1] was not found');
			$msg = str_replace('$1', 'ozanhazer/Htpasswd.php', $msg);

			$arr['status'] = '0';
			$arr['message'] = $msg;
		}

		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: application/json');
		echo json_encode($arr, JSON_PRETTY_PRINT);

		die();
	}
}
