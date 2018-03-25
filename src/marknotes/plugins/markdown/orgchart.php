<?php
/**
 * Convert a list item into an organisational chart
 *
 * Example : convert list items between the two ORGCHART tags
 * into a chart. Also requires a HTML plugin to add JS and CSS
 *
 *		%ORGCHART_START%
 *		* Animals
 *		    * Birds
 *			* Mammals
 *				* Elephant
 *				* Mouse
 *			* Reptiles
 * 		%ORGCHART_END%
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Orgchart extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.orgchart';
	protected static $json_options = 'plugins.options.markdown.orgchart';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		if (preg_match("/^\%ORGCHART_START\%([\s\S]*?)\%ORGCHART_END\%/m", $params['markdown'], $match)) {
			list($tag, $chart) = $match;

			$aeSettings = \MarkNotes\Settings::getInstance();

			$file = "Parsedown.php";
			$folder = $aeSettings->getFolderLibs()."erusev/parsedown/";
			require_once($folder.$file);

			$parsedown = new \Parsedown();
			$chart = $parsedown->text(trim($chart));
			unset($lib);

			$chart =
				'<ul id="mn_orgchart" class="hide">'.
				// Don't take the first four characters
				// i.e. don't take the first "<ul>" tag
				substr($chart,4);

			$md = str_replace($tag, $chart, $params['markdown']);

			$params['markdown'] = $md;
		}

		return true;
	}
}
