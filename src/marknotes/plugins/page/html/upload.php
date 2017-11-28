<?php
/**
 * Add CSS and JS for the upload drop area
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Upload extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.upload';
	protected static $json_options = '';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		// Add JS only if needed based on parameters
		$aeSettings = \MarkNotes\Settings::getInstance();

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/upload/';

		$script = "";

		$script.="<script type=\"text/javascript\" ".
		"src=\"".$url."libs/dropzone/dropzone.min.js\" ".
		"defer=\"defer\"></script>\n";

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

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/upload/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/dropzone/dropzone.min.css\">\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Modify the HTML rendering of the note
	 */
	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		return true;
	}
}
