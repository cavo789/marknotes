<?php
/**
 * Render the code for displaying a login form and, too, verify the
 * submitted credentials if correct.
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Login  extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.login';
	protected static $json_options = JSON_OPTIONS_LOGIN;

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(), '/');

		$script =
			"<script type=\"text/javascript\" ".
			"src=\"".$root."/marknotes/plugins/page/html/login/login.js\" ".
			"defer=\"defer\"></script>";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(), '/');

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\"". "href=\"".$root."/marknotes/plugins/page/html/login/login.css\">\n";

		$css .= $aeFunctions->addStyleInline($script);

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

			// Should the login plugin be active ?
			$bEnabled = boolval(self::getOptions('enabled', false));

			if ($bEnabled) {
				// Yes

				$login = trim(self::getOptions('username', ''));
				$password = trim(self::getOptions('password', ''));

				// If both login and password are empty (will probably be the
				// case on a localhost server), there is no need to add
				// the Login button
				if (($login !== '') && ($password !== '')) {
					$bCanRun = true;
				/*<!-- build:debug -->*/
				} else {
					$aeSettings = \MarkNotes\Settings::getInstance();
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log("The login and/or the password is empty, ".
							"the login form is therefore disabled", "warning");
					}
				/*<!-- endbuild -->*/
				}
			} // if ($bEnabled)
		}

		return $bCanRun;
	}
}
