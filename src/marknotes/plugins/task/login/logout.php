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

		$aeSettings = \MarkNotes\Settings::getInstance();

		// Check if the cache is enable
		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;

		if ($bCache) {
			try {
				// Clear the cache since the cache probably
				// contains informations that was only accessible
				// to connected people; now, need to contains only
				// public features; no more connected ones
				// (like buttons on the toolbar; now should only
				// contain buttons accessible publicly and not more
				// for connected people
				$aeCache = \MarkNotes\Cache::getInstance();
				$aeCache->clear();
			} catch (\Exception $e) {
			}
		}

		header('Content-Type: application/json');
		echo json_encode(array('status' => 1));

		return true;
	}
}
