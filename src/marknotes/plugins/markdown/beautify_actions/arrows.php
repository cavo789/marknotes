<?php
/**
 * Replace "ascii" arrows to typographic ones
 */
namespace MarkNotes\Plugins\Markdown\Beautify_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class Arrows
{
	public function doIt(array $params) : string
	{
		$markdown = $params['markdown'];
		$markdown = str_replace(' --> ', ' → ', $markdown);
		$markdown = str_replace(' <-- ', ' ← ', $markdown);
		$markdown = str_replace(' <--> ', ' ↔ ', $markdown);
		$markdown = str_replace(' ==> ', ' ⇒ ', $markdown);
		$markdown = str_replace(' <== ', ' ⇐ ', $markdown);
		$markdown = str_replace(' <==> ', ' ⇔ ', $markdown);

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
