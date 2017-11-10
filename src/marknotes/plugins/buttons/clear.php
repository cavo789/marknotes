<?php
/**
 * Add a Clear cache button to the menu
 * The 'clear cache' button will be added only when there is something to clear i.e.
 * when
 *    * localStorage property is enabled (i.e. cache on the client side)
 *    * server_session property is enabled (i.e. cache on the server)
 *
 * These properties can be set in the settings.json file, plugins -> options -> optimisation
 *
 * If both are unset (i.e. equals to zero), the 'clear cache' button won't be displayed
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
			// We can continue
			$bReturn = false;

			$aeSettings = \MarkNotes\Settings::getInstance();
			// The optimize plugin should be enabled in settings.json otherwise
			// no need to add the clear button since nothing is cached

			$arr=$aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);

			$bLocalStorage = boolval($arr['localStorage'] ?? false);
			$bServerSession = boolval($arr['server_session'] ?? false);

			// If there is no cache (on the client-side with localStorage
			// or on the server side), the Clear cache button isn't needed
			if (($bLocalStorage !== false) || ($bServerSession !== false)) {
				$bReturn = true;
			}
		} // if ($bReturn = parent::canAdd())

		return $bReturn;
	}
}
