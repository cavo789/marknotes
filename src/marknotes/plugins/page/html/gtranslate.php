<?php
/**
 * Google Translate plugin for Marknotes
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class GTranslate extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.gtranslate';
	protected static $json_options = 'plugins.options.page.html.gtranslate';

	/**
	 * Inject an element in the HTML
	 */
	public static function doIt(&$html = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$div = "<div id=\"google_translate_element\"></div>";

		$html = str_replace('<body>', '<body>'.$div, $html);
		return true;
	}

	/**
	 * Provide additionnal css
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

		$script = "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$root."/marknotes/plugins/page/html/gtranslate/gtranslate.css\" />\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

		$script =
			"\n<script type=\"text/javascript\" ". "src=\"//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit\" defer=\"defer\"></script>\n".
			"<script type=\"text/javascript\" ". "src=\"".$root."/marknotes/plugins/page/html/gtranslate/gtranslate.js\" ".
			"defer=\"defer\"></script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

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

			// Suppose there is a problem (not on localhost f.i.)
			$bCanRun = false;

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
					$aeDebug->log("GTranslate - running on localhost, don't load", "warning");
				}
			}
			/*<!-- endbuild -->*/

			$bCanRun = $bLoad;
		}

		return $bCanRun;
	}
}
