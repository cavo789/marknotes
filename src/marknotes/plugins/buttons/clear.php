<?php
/**
 * Add a Clear cache button to the menu
 * The 'clear cache' button will be added only when there is
 * something to clear i.e. when
 *		* localStorage property is enabled (cache on the client side)
 *		* server_session property is enabled (using $_SESSION)
 *		* cache is enabled (i.e. cache on the server)
 *
 * These properties can be set in the settings.json file,
 * plugins -> options -> optimisation
 *
 * If both are unset (i.e. equals to zero), the 'clear cache'
 * button won't be displayed
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Clear extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.clear';
	protected static $json_linked = '';

	public static function add(&$buttons = array()) : bool
	{
		$buttons['app'][] = self::button(
			array(
				'name' => 'clear',
				'title' => 'settings_clean',
				'default' => 'Clear cache',
				'task' => 'fnPluginTaskOptimizeClearCache',
				'id' => 'icon_settings_clear',
				'icon' => 'eraser'
			)
		);

		return true;
	}

	protected static function canAdd() : bool
	{
		if ($bReturn = parent::canAdd()) {

			// This button can't be displayed to visitors
			$aeSession = \MarkNotes\Session::getInstance();
			$bReturn = boolval($aeSession->get('authenticated', 0));

			if ($bReturn) {
				$aeSettings = \MarkNotes\Settings::getInstance();

				// The optimize plugin should be enabled in
				// settings.json otherwise no need to add the clear
				// button since nothing is cached

				$arr=$aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);

				$bLocalStorage = boolval($arr['localStorage'] ?? false);
				$bServerSession = boolval($arr['server_session'] ?? false);
				$bCache  = boolval($arr['cache']['enabled'] ?? false);

				// If at least one is set, the button Clear cache is
				// usefull so show it
				if ($bLocalStorage || $bServerSession || $bCache) {
					$bReturn = true;
				}
			} // if ($bReturn)
		} // if ($bReturn = parent::canAdd())

		return $bReturn;
	}
}
