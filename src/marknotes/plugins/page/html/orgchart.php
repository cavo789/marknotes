<?php
/**
 * Orgchart
 *
 * @http://capricasoftware.co.uk/#/projects/orgchart/tutorial/basic-chart
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Orgchart extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.orgchart';
	protected static $json_options = 'plugins.options.page.html.orgchart';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/orgchart/';

		$script = "<script ".
			"src=\"".$url."libs/orgchart/jquery.orgchart.min.js\" ". "defer=\"defer\"></script>\n".
			"\n<script>\n".
			"$('document').ready(function(){\n".
				"$('#mn_orgchart').orgChart({\n".
					"container: $('#mn_chart')\n".
				"});".
			"});\n".
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
		$url .= '/marknotes/plugins/page/html/orgchart/libs/orgchart/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$url."jquery.orgchart.css\">\n";

		$script.="<style>\n".
			"div.orgChart { background-color:transparent;}\n".
			"div.orgChart td { border: 1px solid transparent;}\n".
			"div.orgChart table tr:nth-child(2n) { background-color: transparent;}\n".
			"div.orgChart table { margin-bottom: 0;}\n".
			"</style>";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		// Check if a chart is present : look for the
		// "mn_orgchart" ID
		if (strpos($html, 'id="mn_orgchart"') !== false) {
			$dom = new \DOMDocument;

			$dom->preserveWhiteSpace = false;
			$dom->encoding = 'utf-8';

			@$dom->loadHTML(utf8_decode($html));

			// Get the UL with the "mn_orgchart" id.
			$chart = $dom->getElementById('mn_orgchart');

			// And add a <div id="mn_chart"> just before the list
			$div = $dom->createElement('div');
			$div->setAttribute('id', 'mn_chart');
			$chart->parentNode->insertBefore($div,$chart);

			$html = $dom->saveHTML($dom->documentElement);
		}
		return true;
	}
}
