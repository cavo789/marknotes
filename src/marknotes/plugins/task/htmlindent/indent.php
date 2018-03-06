<?php
/**
 * HTML indent
 * @src : https://github.com/vanilla/htmlawed/tree/master/src/htmLawed
 */
namespace MarkNotes\Plugins\Task\HTMLIndent;
defined('_MARKNOTES') or die('No direct access allowed');
class Indent extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.htmlindent';
	protected static $json_options = 'plugins.content.html.htmlindent';
	public static function run(&$params = null) : bool
	{
		if (self::isEnabled(false)) {
			// Ok, the task is enabled
			$lib = __DIR__.'/libs/htmLawed.php';

			if (file_exists($lib)) {

				require_once($lib);
				$html = $params['html']??'';
				if (trim($html)==='') {
					return true;
				}

				// Get the indent size from settings.json
				$size = self::getOptions('indent_size', 8);
				$config = array('tidy'=>$size);
				$params['html'] = \htmLawed($html, $config);
			}
		}
		return true;
	}
}
