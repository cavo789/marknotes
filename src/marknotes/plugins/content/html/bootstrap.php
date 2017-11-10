<?php
/**
 * Make tables responsive
 *
 * 	"plugins": {
 * 		"options": {
 *			"content": {
 *	 			"html": {
 *	 				"bootstrap": {
 *		 				"tables": {
 *		 				 	"extra-classes": "table table-bordered table-hover",
 *	 				 		"responsive": 1
 *	 					}
 * 					}
 * 				}
 * 			}
 * 		}
 * 	}
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Bootstrap extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.bootstrap';
	protected static $json_options = 'plugins.options.content.html.bootstrap';

	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		$arrOptions = self::getOptions('tables', array());

		$responsive = boolval($arrOptions['responsive'] ?? 1);
		$classes = trim($arrOptions['extra-classes'] ?? 'table');

		// Make tables responsive
		if ($responsive) {
			// It's safe to work on "<table>" because $content is the
			// HTML conversion of a markdown file and no extra attributes
			// are created for a table; just, yes, the <table> tag.
			$content = str_replace('<table>', '<div class="table-responsive"><table>', $content);
			$content = str_replace('</table>', '</table></div>', $content);
		}

		// Make tables responsive
		if ($classes!=='') {
			$content = str_replace('<table>', '<table class="'.$classes.'">', $content);
		}

		return true;
	}
}
