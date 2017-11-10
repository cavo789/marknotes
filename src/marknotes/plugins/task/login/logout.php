<?php
/**
 * Process logouts
 */
namespace MarkNotes\Plugins\Task\Login;

defined('_MARKNOTES') or die('No direct access allowed');

class Logout extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.login';
	protected static $json_options = JSON_OPTIONS_LOGIN;

	public static function run(&$params = null) : bool
	{
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSession->set('authenticated', 0);
		$aeSession->destroy();

		header('Content-Type: application/json');
		echo json_encode(array('status' => 1));

		return true;
	}
}
