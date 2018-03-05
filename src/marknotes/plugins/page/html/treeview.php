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

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/treeview/';

		$script = "\n<script ".
			"src=\"".$url."libs/jstree/jstree.min.js\" ".
			"defer=\"defer\"></script>\n";

		$script .= "<script ".
			"src=\"".$url."jstree.js\" ".
			"defer=\"defer\"></script>\n";

		// Only when the user is connected
		if ($aeSession->get('authenticated', 0) === 1) {
			// Add extra functionnalities like adding, renaming or removing
			// a folder / a note
			$script .= "<script ".
				"src=\"".$url."treeview.js\" ".
				"defer=\"defer\"></script>";
		} // if ($aeSession->get('authenticated', 0) === 1)

		$theme = self::getOptions('theme', 'default');

		$js .= "<script>\n".
			"marknotes.jstree={};\n".
			"marknotes.jstree.theme='".$theme."';\n".
			"</script>\n";

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
		$url .= '/marknotes/plugins/page/html/treeview/';

		$theme = self::getOptions('theme','default');

		// Load first the default.css style since not every styles
		// have been overwritten
		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/jstree/themes/default/style.min.css\">\n";

		if ($theme !=='default') {
			$script .=
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
				"href=\"".$url."libs/jstree/themes/".$theme."/style.min.css\">\n";
		}

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
