<?php
/**
 * Google Analytics plugin for Marknotes
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class GAnalytics extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.ganalytics';
	protected static $json_options = 'plugins.options.page.html.ganalytics';

	/**
	 * Add new scripts in the <script> part of the page; add the Google Analytics script
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		// Don't load if no code has been provided
		$analyticsCode = trim(self::getOptions('code', ''));

		// Get the template for the JS script of Google Analytics
		$script = file_get_contents(__DIR__.'/ganalytics/code.txt');

		// And use the user's code
		$script = str_replace('%AnalyticsCode%', $analyticsCode, $script);

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		return true;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		return true;
	}

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			$aeSettings = \MarkNotes\Settings::getInstance();

			// Suppose there is a problem (no code provided or on localhost)
			$bCanRun = false;

			// Don't load if no code has been provided
			$analyticsCode = trim(self::getOptions('code', ''));

			if ($analyticsCode!=='') {
				// Check if, in the settings, enable_localhost is set to 1 (default value)
				// If set to 0, don't load the plugin on localhost system
				$localhost = boolval(self::getOptions('enable_localhost', 1));

				// localhost is equal to 1 ? Always load the plugin
				$bLoad = ($localhost == 1);

				if (!$bLoad) {
					// Check if we're on localhost, if so, don't load the plugin
					$bLoad = !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1','::1'));

					// Check name too
					if ($bLoad) {
						$bLoad = ($_SERVER['SERVER_NAME'] !== 'localhost');
					}
				}

				/*<!-- build:debug -->*/
				if (!$bLoad) {
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log("GAnalytics - running on localhost, don't load", "warning");
					}
				}
				/*<!-- endbuild -->*/

				if ($bLoad) {
					// Finally check the existence of __DIR__.'/ganalytics/code.txt'

					if (is_file($fname = __DIR__.'/ganalytics/code.txt')) {
						$bCanRun =  true;
					/*<!-- build:debug -->*/
					} else {
						if ($aeSettings->getDebugMode()) {
							$aeDebug = \MarkNotes\Debug::getInstance();
							$aeDebug->log("File [".$fname."] not found", "warning");
						}
					/*<!-- endbuild -->*/
					}
				}
			/*<!-- build:debug -->*/
			} else { // if ($analyticsCode!=='')
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("GAnalytics plugin enabled but no code provided", "warning");
				}
			/*<!-- endbuild -->*/
			}
		}

		return $bCanRun;
	}
}
