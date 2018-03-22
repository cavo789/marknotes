<?php
/**
 * When exporting a note to a DOCX, PDF, ... file, the %TOC_99% tag
 * (=insert a table of content) shouldn't be interpreted since
 * the pandoc converter already add such table so just remove the tag.
 *
 * This markdown content plugin will just disable the TOC tag
 * (if needed), the creation of the table will be done by the HTML
 * content plugin
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class TOC extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.toc';
	protected static $json_options = '';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$aeSession = \MarkNotes\Session::getInstance();
		$task = $aeSession->get('task');

		// The %TOC ...% tag should start the line. If there is one or
		// more character before
		// (for instance a single space) consider the tag not active.
		// So " %TOC ..." won't work and can be therefore use to
		// temporary disable the tag f.i.

		if (preg_match("/^([ \\t])*%TOC_(\\d)%/m", $params['markdown'], $match)) {
			// $tag	=> $matches[0][0] will be f.i. " %TOC_6"
			// $before => $matches[1][0] will be f.i. " "
			// 		What's before %TOC
			// $deep	=> $matches[2][0] will be f.i. "6"
			list($tag, $before, $deep) = $match;

			$aeSettings = \MarkNotes\Settings::getInstance();

			if (trim($before)=='') {
				// Only if nothing was before the tag so :
				// the tag is active
				// For DOCX / PDF / TXT : remove the tag; table of
				// content will be added by the convertor (DOCX/PDF)
				// or has no sense (TXT)
				$arr = array('task.export.docx','task.export.pdf','task.export.txt');

				if (in_array($task, $arr)) {
					$params['markdown'] = str_replace($tag, '', $params['markdown']);
					return false;
				}
			} else {
				// Due to the conversion from markdown to HTML,
				// the line %TOC_6% will be converted to
				// <p>%TOC_6</p> so the empty characters
				// will be trimmed. It's ok in a normal way of
				// working but if we want to be able to disable
				// the %TOC% tag, we then need to modify it a little.
				//
				// TOC_disable means therefore that the tag
				// isn't doesn't start the line and therefore not
				// activated.
				// Just remove the space and keep a line like
				//
				// %TOC_6%
				//
				// to enable it again.

				$arr = array('task.export.html','main','task.export.md');
				if (!in_array($task, $arr)) {
					// Don't show the tag, if disabled, for f.i.
					// html/pdf/... output
					$disabled = '';

					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log('Disable TOC plugin before the %TOC% tag isn\'t at the begining of the sentence', 'debug');
						$aeDebug->log('	***'.$tag.'***', 'debug');
					}
					/*<!-- endbuild -->*/
				} else {
					$disabled = str_replace('%TOC_', '%TOC_disabled_', $tag);
				}

				// There is at least one space before %TOC
				// ==> don't activate the plugin
				$params['markdown'] = str_replace($tag, $disabled, $params['markdown']);
				return false;
			}
		}

		return true;
	}
}
