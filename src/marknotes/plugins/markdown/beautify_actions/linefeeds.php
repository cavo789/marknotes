<?php
/**
 * With markdown language there is no need to have more
 * than three linefeed (no empty lines)
 */
namespace MarkNotes\Plugins\Markdown\Beautify_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class LineFeeds
{
	public function doIt(array $params) : string
	{
		$markdown=preg_replace('/\n{3,}/si', "\n\n", $params['markdown']);
		// and no need to have a line with space characters
		$markdown=preg_replace('/\n {1,}\n/si', "\n\n", $markdown);

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
