<?php
/**
 * Add the Datatable jQuery to the page and make html table very powerfull
 * by adding filtering, search feature, column sort, pagination, ...
 * @link https://github.com/DataTables/DataTables
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class DataTables extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.datatables';
	protected static $json_options = 'plugins.options.page.html.datatables';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Website rool URL like f.i. http://localhost/notes
		$root = rtrim($aeFunctions->getCurrentURL(false, false), '/');

		//$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url = $root. '/marknotes/plugins/page/html/datatables/';
		$urlLib = $url.'libs/datatables/js/';

		$script = "<script type=\"text/javascript\" ".
			"src=\"".$urlLib."jquery.dataTables.min.js\" defer=\"defer\"></script>\n".
			"<script type=\"text/javascript\" ". "src=\"".$urlLib."dataTables.bootstrap4.min.js\" defer=\"defer\"></script>\n".
			"<script type=\"text/javascript\" ". "src=\"".$urlLib."dataTables.buttons.min.js\" defer=\"defer\"> ".
			"</script>\n".
			"<script type=\"text/javascript\" src=\"".$urlLib."buttons.html5.min.js\" ". "defer=\"defer\"></script>\n".
			"<script type=\"text/javascript\" ". "src=\"".$url."datatables.js\" ".
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

		$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url .= '/marknotes/plugins/page/html/datatables/';
		$urlLib = $url.'libs/datatables/css/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$urlLib."dataTables.bootstrap4.min.css\">\n".
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". "href=\"".$urlLib."buttons.bootstrap.min.css\">\n".
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". "href=\"".$urlLib."buttons.dataTables.min.css\">\n";

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
