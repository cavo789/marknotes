<?php
/**
 * Add jsTree scripts and, when the user is connected (or when no
 * authentication is needed), add extra functionnalities in the contextual
 * menu of the treeview i.e. when the user right-clic on the treeview
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Treeview extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.treeview';
	protected static $json_options = 'plugins.options.page.html.treeview';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url .= '/marknotes/plugins/page/html/treeview/';

		$script = "\n<script type=\"text/javascript\"".
			"src=\"".$url."libs/jstree/jstree.min.js\"".
			"defer=\"defer\"></script>\n";

		$script .= "<script type=\"text/javascript\"".
			"src=\"".$url."jstree.js\"".
			"defer=\"defer\"></script>\n";

		// Only when the user is connected
		if ($aeSession->get('authenticated', 0) === 1) {
			// Add extra functionnalities like adding, renaming or removing
			// a folder / a note

			$script .= "<script type=\"text/javascript\"".
				"src=\"".$url."treeview.js\"".
				"defer=\"defer\"></script>";
		} // if ($aeSession->get('authenticated', 0) === 1)

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

		$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url .= '/marknotes/plugins/page/html/treeview/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/jstree/themes/proton/style.min.css\">\n";

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
}
