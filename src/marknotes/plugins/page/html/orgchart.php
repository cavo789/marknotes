<?php
/**
 * Orgchart - Convert a bullet list items into an organisational
 * chart. The list should be prefixed on his own line by
 * %ORGCHART_START% then the list and followed by an empty line
 * and, on his own line, by %ORGCHART_END%. See the sample here below
 *
 * @https://github.com/dabeng/OrgChart
 *
 * Sample :
 *
 *		%ORGCHART_START%
 * 		* The animal world
 *			* Invertebrates
 *				* Molluscs
 *					* Cephalopods
 *					* Gastropods
 *					* Bivalves
 *				* Crustaceans
 *			* Vertebrates
 *				* Fish
 *					* Bone
 *					* Cartilaginous
 *
 *		%ORGCHART_END%
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
			"<script ".
				"src=\"".$url."libs/orgchart/html2canvas.min.js\" ". "defer=\"defer\"></script>\n".
			"\n<script>\n".
			"$('document').ready(function(){\n".
				"$('#mn_chart-container').orgchart({\n".
					"'data' : $('#mn_orgchart-data'),\n".
					"'verticalLevel': 3,\n".
					"'visibleLevel' : 3,\n".
					"'exportButton': true,\n".
					"'exportFilename': 'mn_Chart'\n".
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
			"#mn_chart-container {text-align: center;}\n".
			".orgchart { background: #fff; }\n".
			"#mn_chart-container .orgchart .lines td { height : 0; }\n".
			".markdown-body table th, .markdown-body table td { border: none; }\n".
			".orgchart td { padding: initial !important; }\n".
			".orgchart table tr { background-color: transparent !important; height: 10px;}\n".
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
		if (strpos($html, '%ORGCHART_START%') !== false) {

			$pattern='/<p.*>\%ORGCHART_START\%<\/p>/';
			preg_match($pattern, $html, $matches, PREG_OFFSET_CAPTURE);

			// Get the lenght of the tag
			// <p (class and ids)>\%ORGCHART_START\%</p>
			$startLen = strlen($matches[0][0]);

			// Get the start position of
			// <p (class and ids)>\%ORGCHART_START\%</p>
			$startPos = $matches[0][1];

			// Get the start position of
			// <p (class and ids)>\%ORGCHART_END\%</p>
			$pattern='/<p.*>\%ORGCHART_END\%<\/p>/';
			preg_match($pattern, $html, $matches, PREG_OFFSET_CAPTURE);

			if ($matches == array()) {
				// There is a problem !!!
				// The %ORGCHART_END% tag wasn't found.
				// If this happens, add an empty line, in the
				// markdown file between the last org chart data
				// and the %ORGCHART_END% line
				//
				// So, add an empty line before %ORGCHART_END%
				// like here below
				//
				// 		%ORGCHART_START%
				//		* Root level
				//			* level 1
				//			* level 2
				//
				// 		%ORGCHART_END%
				return false;
			}

			$endLen = strlen($matches[0][0]);
			$endPos = $matches[0][1];

			// Extract the original HTML i.e.
			//		<p id="par_0">%ORGCHART_START%</p>
			//			<ul>
			//				<li>Animals
			//					<ul>
			//						<li>Birds</li>
			//						<li>Mammals</li>
			//					</ul>
			// 		...
			//		<p id="par_1">%ORGCHART_END%</p>

			$original = substr($html, $startPos, $endPos - $startPos + $endLen);

			// Retrieve only the organisational data
			$len = $endPos - $startPos;
			$orgData = substr($html, $startPos + $startLen, $endPos - $startPos - $startLen);

			// Remove every <i class="fa-li fa fa-check"></i>
			// because https://github.com/dabeng/OrgChart didn't
			// work correctly in that case. The <li> item should
			// only have the content like <li>VALUE</li>
			$pattern = "~<li>\\s?<i.*\/i>?~m";
			$orgData = preg_replace($pattern, '<li>', $orgData);

			// Remove CSS classes
			$pattern = "~ class=\".*\"~";
			$orgData = preg_replace($pattern, '', $orgData);

			// Add an ID to the org data info (i.e. to the
			// <ul><li>...</li></ul> block
			$orgData = '<ul class="hide" id="mn_orgchart-data"'.substr($orgData, 4);

			// Now, add the DIV for the chart
			$orgData = '<div id="mn_chart-container"></div>'.$orgData;

			// Finally, replace the original data to the
			// correctly prepared org chart structure (div and
			// ul li)
			$html = str_replace($original, $orgData, $html);
		}

		return true;
	}
}
