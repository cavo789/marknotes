<?php
/**
 * Intro.js - Disable the plugin
 *
 * Answer to URL like index.php?task=task.introjs.disable
 */
namespace MarkNotes\Plugins\Task\Favorites;

defined('_MARKNOTES') or die('No direct access allowed');

class Disable extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.introjs';
	protected static $json_options = '';

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();


		$arrSettings = $aeSettings->getPlugins('/interface', array('demo_mode'=>0));
		$demo = boolval($arrSettings['demo_mode'] ?? 0);

		$arr = array();

		if ($demo) {
			// Demo mode enabled, return an info
			$arr['message'] = $aeSettings->getText('demo_mode_enabled', '');
			$arr['status'] = '1';
		} else {
			$rootFolder = $aeSettings->getFolderWebRoot();

			$arrSettings = array();

			// If there is already a settings.json file,
			// get its content
			if ($aeFiles->exists($json = $rootFolder.'settings.json')) {
				$arrSettings = json_decode($aeFiles->getContent($json), true);
			}

			$arrNew = array();
			$arrNew['plugins'] = array();
			$arrNew['plugins']['page'] = array();
			$arrNew['plugins']['page']['html'] = array();
			$arrNew['plugins']['page']['html']['introjs'] = array();
			$arrNew['plugins']['page']['html']['introjs']['enabled'] = 0;

			// And merge it with the new settings
			if (count($arrSettings)>0) {
				$arrSettings = array_replace_recursive($arrSettings, $arrNew);
			} else {
				$arrSettings = $arrNew;
			}

			// Write the file
			$aeFiles->rewrite($json, json_encode($arrSettings, JSON_PRETTY_PRINT));

			// Prepare the JSON
			$arr['message'] = $aeSettings->getText('intro_js_disabled', '');
			$arr['status'] = '1';
		}

		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: application/json');
		echo json_encode($arr, JSON_PRETTY_PRINT);
		die();
	}
}
