<?php
/**
* Be sure to have content with LF and not CRLF in order to be able to use
* generic regex expression (match \n for new lines)
 */
namespace MarkNotes\Plugins\Markdown\Beautify_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class CRLF
{
	public function doIt(array $params) : string
	{
		$markdown = $params['markdown'];
		$markdown = str_replace("\r\n", "\n", $markdown);

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
