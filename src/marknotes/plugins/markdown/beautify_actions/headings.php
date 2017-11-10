<?php
/**
 * No need to have more than one space between a title (#) and the text
 * So replace "###    My title" by "### My title"
 */
namespace MarkNotes\Plugins\Markdown\Beautify_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class Headings
{
	public function doIt(array $params) : string
	{
		// $1 will match the # char(s) (so "###")
		// $2 will match spaces  (so "    " in the example)
		// $3 will match the title (so "My title")
		//
		// So, the result is :
		// "$1 $3"   ==> give the headings followed by only one space followed by title

		$markdown = $params['markdown'];
		$markdown = preg_replace('/^(#{1,})( ){2,}(.*)/m', "$1 $3", $markdown);

		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			if ($params['markdown']!==$markdown) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("   ".__CLASS__." has modify the markdown content", "debug");
			}
		}
		/*<!-- endbuild -->*/

		return $markdown;
	}
}
