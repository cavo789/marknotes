<?php
/**
 * Optimize the content
 *
 * Will be enabled when :
 *	 * plugins.content.html.optimize is enabled
 *	 * plugins.page.html.optimize is enabled
 *	 * plugins.options.page.html.optimize.images.lazyload is enabled
 *
 * Won't be enabled otherwise
 *
 * Note : the optimization can be disabled on the URL by using
 *		the "optimization" parameter. Setting it to 0 will disable
 *		the optimization even if well enabled in settings.json
 *
 * Example index.php?task=task.export.html&param=xxxxx&optimization=0
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Optimize extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.optimize';
	protected static $json_options = JSON_OPTIONS_OPTIMIZE;

	public static function doIt(&$html = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		// URL to the blank image
		$img = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$img .= 'marknotes/plugins/page/html/optimize/images/blank.png';

		$pattern = '/(<img [^>]*)src=["|\']([^"|\']+)["|\']/';

		if (preg_match_all($pattern, $html, $matches)) {

			list ($tag, $img_before, $img_src) = $matches;

			for ($i = 0; $i < count($matches[0]); $i++) {

				// Replace the original image to the blank one
				// And add extra attributes
				$extra = "src=\"".$img."\" class=\"lazyload\" data-src=\"".$img_src[$i]."\"";

				$html = str_replace($tag[$i], $img_before[$i].$extra, $html);
			}
		}

		return true;
	}

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {

			$aeFunctions = \MarkNotes\Functions::getInstance();

			// Check if the optimization parameter is set
			// on the querystring and if yes, don't run the
			// optimize plugin if set to 0
			$bCanRun = $aeFunctions->getParam('optimization', 'boolean', true);

			if ($bCanRun) {

				// This plugin is only needed when, in settings.json,
				// the key
				// plugins.options.page.html.optimize.images.lazyload
				// is set to 1 i.e. active
				$arrOptions = self::getOptions('images', array());
				$bCanRun = boolval($arrOptions['lazyload'] ?? 1);

				// Ok, the lazyload feature is enabled but, perhaps, the optimize
				// plugin not.
				if ($bCanRun) {
					$aeSettings = \MarkNotes\Settings::getInstance();
					$arrSettings = $aeSettings->getPlugins('plugins.page.html.optimize');
					$bCanRun = $arrSettings['enabled'] ?? 0;
				}
			}
		}

		return $bCanRun;
	}
}
