<?php
/**
 * DISABLED since in a code bloc the & should remains unchanged.
 * The following bloc should remains with & and this amp.php
 * action, if enabled, will display &amp; in the code bloc in
 * a html or reveal format. And shouldn't be the case.
 *
 * ```php
 *	  if(this && that) {
 * ```
 *
 *
 * In the edit form, the single "&" character won't be correctly
 * interpreted. The regex here below will retrieve every & and not &amp;
 * If there are occurences, replace & by &amp;
 */
namespace MarkNotes\Plugins\Markdown\Beautify_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class AMP
{
	public function doIt(array $params) : string
	{
		$markdown = $params['markdown'];

		if (preg_match_all('/&(?![A-Za-z]+;|#[0-9]+;)/m', $markdown, $matches)) {
			foreach ($matches as $match) {
				$markdown = str_replace($match, '$$$$', $markdown);
				$markdown = str_replace('$$$$', '&amp;', $markdown);
			}
		}

		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			if ($params['markdown']!==$markdown) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("	".__CLASS__." has modify the markdown content", "debug");
			}
		}
		/*<!-- endbuild -->*/

		return $markdown;
	}
}
