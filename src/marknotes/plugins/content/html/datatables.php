<?php
/**
 * Because datatables.js is always loaded and, because we can
 * disable the databatables plugin for a given folder / note
 * (override of settings.json); this is needed to
 * communicate to the js script that "this time" the html table can be
 * converted to a datatables one.
 *
 * This is simply done by : when database.php is enabled, add the
 * attribute below (i.e. data-datatables-enable="1"). The js script
 * will examine every tables and will only process the ones with
 * that attribute on 1.
 *
 * If the datatables.php plugin isn't enabled, then this code isn't
 * fired and the attribute doesn't exists. Even if datatables.js is
 * loaded, nothing will be done.
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class DataTables extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.options.content.html.datatables';
	protected static $json_options = '';

	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return false;
		}

		// Detect if the plugin is enabled or not
		$aeSettings = \MarkNotes\Settings::getInstance();
		$arrSettings = $aeSettings->getPlugins(static::$json_settings);

		// Get the flag
		$enabled = boolval($arrSettings['enabled'] ?? 0);
		$enabled = 'data-datatables-enable="'.($enabled ? 1 : 0).'"';

		$content = str_replace('<table', '<table '.$enabled, $content);

		return true;
	}

	protected static function canRun() : bool
	{
		// This plugin is an exception : should always be fired
		// because if the plugin is disabled for a specify note,
		// a data-attribute should be specified to 0
		return true;
	}
}
