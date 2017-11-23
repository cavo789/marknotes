<?php
/**
 * Add the marknotes ascii banner in html files
 *
 *	This plugin has no option
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Banner extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.banner';
	protected static $json_options = '';

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		return true;
	}

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		return true;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		if (trim($html) === '') {
			return true;
		}

		if (file_exists($fname = __DIR__.'/banner/banner.txt')) {
			$banner = file_get_contents($fname);

			$aeSettings = \MarkNotes\Settings::getInstance();
			if ($aeSettings->getDebugMode()) {
				$banner = "\n<!-- Lines below are added by".__FILE__."-->\n".
					trim($banner, "\n")."\n".
					"<!-- End for ".__FILE__."-->\n";
			}
			$html = str_replace('<head>', '<head>'.$banner, $html);
		}

		return true;
	}
}
