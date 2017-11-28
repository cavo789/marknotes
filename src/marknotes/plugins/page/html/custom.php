<?php
/**
 * This plugin will allow to load custom css and js files
 *
 * In settings.json, just add this section :
 *
 * "plugins": {
 *		"options": {
 *			"page" : {
 *				"html" : {
 *					"custom" : {
 *						"css" : [
 *							"your_own.css",
 *							"/assets/a_second_one.css"
 *						],
 *						"js" : [
 *							"your_own.js",
 *							"/assets/a_second_one.js"
 *						]
 *					}
 *				}
 *			}
 *		}
 *	}
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Custom extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.custom';
	protected static $json_options = 'plugins.options.page.html.custom';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$arrOptions = self::getOptions('js', array());

		if ($arrOptions!==array()) {
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$root = rtrim($aeFunctions->getCurrentURL(), '/').'/';

			$sScript = '';

			foreach ($arrOptions as $file) {
				// Remove the directory separator if any
				$file = ltrim($file, "/\\");

				if ($aeFiles->fileExists($fname = $aeSettings->getFolderWebRoot().$file)) {
					$sScript .= "<script type=\"text/javascript\" src=\"".$root.$file."\" ".
						"defer=\"defer\"></script>\n";
				/*<!-- build:debug -->*/
				} else {
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log("The file [".str_replace(DS, "/", $fname)."] is missing", "debug");
					}
				/*<!-- endbuild -->*/
				}
			}

			if (trim($sScript)!=='') {
				$js .= $aeFunctions->addJavascriptInline($sScript);
			}
		}

		return true;
	}

	/**
	 * Provide additionnal css
	 */
	public static function addCSS(&$css = null) : bool
	{
		$arrOptions = self::getOptions('css', array());

		if ($arrOptions!==array()) {
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$root = rtrim($aeFunctions->getCurrentURL(), '/').'/';

			$sScript = '';

			foreach ($arrOptions as $file) {

				// Remove the directory separator if any
				$file = ltrim($file, "/\\");

				if ($aeFiles->fileExists($fname = $aeSettings->getFolderWebRoot().$file)) {
					$sScript = "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
						"href=\"".$root.$file."\"/>";
				/*<!-- build:debug -->*/
				} else {
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log("The file [".str_replace(DS, "/", $fname)."] is missing", "debug");
					}
				/*<!-- endbuild -->*/
				}
			}

			if (trim($sScript)!=='') {
				$css .= $aeFunctions->addStyleInline($sScript);
			}
		}

		return true;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		return true;
	}
}
