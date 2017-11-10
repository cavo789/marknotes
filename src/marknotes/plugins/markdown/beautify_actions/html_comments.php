<?php
/**
 * When working with a markdown file, we can remove HTML comments like
 * <!--  comments -->
 */
namespace MarkNotes\Plugins\Markdown\Beautify_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class HTML_Comments
{
	public function doIt(array $params) : string
	{
		// Remove HTML comments ?
		$bRemove = boolval($params['options']['remove_html_comments'] ?? 1);

		if ($bRemove) {
			// Remove HTML comments

			$markdown = preg_replace(
				'#(?>(?:<(?!!))?[^<]*+(?:<(?:script|style)\b[^>]*+>(?><?[^<]*+)*?<\/(?:script|style)>|<!--\[(?><?[^<]*+)*?'.
				'<!\s*\[(?>-?[^-]*+)*?--!?>|<!DOCTYPE[^>]++>)?)*?\K(?:<!--(?>-?[^-]*+)*?--!?>|[^<]*+\K$)#i',
				'',
				$params['markdown']
			);

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
