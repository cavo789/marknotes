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

		$script = "<script src=\"https://togetherjs.com/togetherjs-min.js\">".
			"</script>\n";

		// See https://togetherjs.com/docs/#configuring-togetherjs,
		// for explanations of configuration options
		$script .= "<script>\n".
			// Used in some help text.
			"TogetherJSConfig_siteName = \"".$sitename."\";\n".
			// This is the name that you are giving this tool.
			// If you use this then "TogetherJS" won't be in the UI
			"TogetherJSConfig_toolName = \"marknotes collaboration\";\n".
			// Name of the room that will appears f.i. in the
			// "invitation" link
			"TogetherJSConfig_findRoom = \"marknotes\";\n".
			// When a person is invited to a session,
			// they'll be asked if they want to join in browsing with
			// the other person. Set this to true and they won't be
			// asked to confirm joining.
			"TogetherJSConfig_suppressJoinConfirmation = true;\n".
			// Don't show the "Start TogetherJS" button,
			// when someone click on the "invitation" link :
			// immediatly start the collaboration session
			"TogetherJSConfig_enableShortcut = true;\n".
			"TogetherJSConfig_includeHashInUrl = true;\n".
			// Initialize the username for TogetherJS by using the
			// username used to establish a connection to the site
			// (within the .htpasswd protection or in the login
			// form f.i.)
			// https://togetherjs.com/docs/#setting-identity-information
			"TogetherJSConfig_getUserName = function () {return marknotes.settings.username;};\n\n".
			//"if (TogetherJS.running) {\n".
			//"	TogetherJS.hub.on(\"edit\", function (data) {\n".
			// "console.log(data);\n".
			//"	  alert(data.info);\n".
			//"	});\n".
			//"}\n".
			"</script>\n";

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
