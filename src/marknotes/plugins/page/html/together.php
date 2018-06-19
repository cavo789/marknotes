<?php
/**
 * Add together.js script of Mozilla for offering multi-users
 * editing
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Together extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.together';
	protected static $json_options = 'plugins.options.page.html.together';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$sitename = str_replace('"', '\"', $aeSettings->getSiteName());

		// See https://togetherjs.com/docs/, Configuration part for
		// explanations of configuration options
		$script = "<script>\n".
			"TogetherJSConfig_siteName = \"".$sitename."\";\n".
			"TogetherJSConfig_toolName = \"marknotes collaboration\";\n".
			"TogetherJSConfig_findRoom = \"marknotes\";\n".
			"</script>\n";

		$script .= "<script src=\"https://togetherjs.com/togetherjs-min.js\" ".
			"defer=\"defer\"></script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal css
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
			$bCanRun = false;

			$aeSession = \MarkNotes\Session::getInstance();

			// Only when the user is connected
			if ($aeSession->get('authenticated', 0) === 1) {
				$bCanRun = true;
			}
		}

		return $bCanRun;
	}
}
