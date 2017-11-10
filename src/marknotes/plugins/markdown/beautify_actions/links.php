<?php
/**
 * Check if the markdown content has links with space like f.i.
 *   [link](%URL%.files/a filename with spaces.pdf)
 * and if so, replace spaces by the "%20" escape character
 */
namespace MarkNotes\Plugins\Markdown\Beautify_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class Links
{
	public function doIt(array $params) : string
	{
		// Don't do this for images ([^!]) because the filename can be
		// followed by a space then a size like in
		//		![test](%URL%.images/logo.png "128x32")
		//
		// The space there is important
		$markdown = $params['markdown'];

		if (preg_match_all('/[^!]([[^].*])\(([^)]*)/m', $markdown, $matches)) {
			list($pattern, $caption, $urls) = $matches;

			for ($i=0; $i < count($pattern); $i++) {
				// Replace the space by a %20
				$replaced = str_replace(' ', '%20', $urls[$i]);

				// Make the replacement in the link
				// 	[link](%URL%.files/a%20filename%20with%20spaces.pdf)
				$replaced = str_replace($urls[$i], $replaced, $pattern[$i]);

				// And finaly do replace in the content
				$markdown = str_replace($pattern[$i], $replaced, $markdown);
			}

			/*<!-- build:debug -->*/
			$aeSettings = \MarkNotes\Settings::getInstance();
			if ($aeSettings->getDebugMode()) {
				if ($params['markdown']!==$markdown) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("   ".__CLASS__." has modify the markdown content", "debug");
				}
			}
			/*<!-- endbuild -->*/
		}

		return $markdown;
	}
}
