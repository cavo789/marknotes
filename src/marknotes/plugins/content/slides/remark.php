<?php

namespace MarkNotes\Plugins\Content\Slides;

defined('_MARKNOTES') or die('No direct access allowed');

class Remark
{
	private static $layout = 'remark';
	/**
	 *
	 */
	public static function doIt(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if ($params['filename'] !== "") {
			$fullname = $aeSettings->getFolderDocs(true).$params['filename'];

			if (!$aeFiles->fileExists($fullname)) {
				$aeFunctions->fileNotFound($fullname);
			}

			// Read the markdown file
			$aeMarkDown = \MarkNotes\FileType\Markdown::getInstance();

			$markdown = $aeMarkDown->read($fullname, $params);

			// Try to retrieve the heading 1
			$pageTitle = $aeMarkDown->getHeadingText($markdown, '#');

			// The slideshow functionnality will be remark

			// Consider that every headings (except h1) should start in a new slide
			// The "remark" library allow indeed to give a name to each slide by just adding "name: NAME" in the markdown string

			$arrHeading = array('##','###','####','#####','######');
			foreach ($arrHeading as $head) {
				$matches = array();

				preg_match_all("/\\n".$head." (.*)/", $markdown, $matches);

				if (count($matches) > 0) {
					// Process one by one
					$j = count($matches[0]);

					for ($i = 0; $i < $j; $i++) {
						// $matches[0][$i] is f.i. "## TITLE" while $matches[1][$i] is "TITLE"
						//
						// remark allow to specify the name of the slide so add a "name:" property in the markdown like this :
						//
						//   name: TITLE
						//   ---
						//   ## TITLE

						$markdown = str_replace(
							$matches[0][$i],
							//"???".PHP_EOL.str_replace('/',DS,$filename).PHP_EOL.  // Add speaker note : ??? followed by a line and the text
							"---".PHP_EOL.
							"name: ".rtrim($matches[1][$i], " #").PHP_EOL.        // Be sure to not have a title like ## Heading2 ## (==> remove final # and space if there are ones)
							".footnote[.italic[".$pageTitle."]]".PHP_EOL.
							$matches[0][$i],
							$markdown
						);
					} // for ($i)
				} // if(count($matches)>0)
			} // foreach ($arrHeading as $head)

			// -------------------
			// Consider an <hr> (can be <hr   >, <hr  />, ...) as a new slide

			$matches = array();
			preg_match_all('/-{3,5}/', $markdown, $matches);
			foreach ($matches[0] as $tmp) {
				$markdown = str_replace($tmp, '---', $markdown);
			}

			// -----------------------
			// Get the remark template
			$slideshow = file_get_contents($aeSettings->getTemplateFile('remark'));

			$html = str_replace('%CONTENT%', strip_tags($markdown), $slideshow);
			$html = str_replace('%SITE_NAME%', $aeSettings->getSiteName(), $html);
			$html = str_replace('%ROOT%', rtrim($aeFunctions->getCurrentURL(true, false), '/'), $html);

			// $slideshow contains the template : it's an html file with (from the /templates folder)
			// and that file contains variables => convert them
			//$aeHTML = \MarkNotes\FileType\HTML::getInstance();
			//$html = $aeHTML->replaceVariables($slideshow, $markdown, $params);
		} // if ($params['filename'] !== "")

		// And return the HTML to the caller
		$params['html'] = $html;
		return true;
	}

	/**
	 * Attach the function and responds to events
	 */
	public function bind(string $plugin)
	{
		$aeSession = \MarkNotes\Session::getInstance();
		$task = $aeSession->get('task', '');

		// Don't attach code if the task is reveal
		if (in_array($task, array('reveal'))) {
			return false;
		}

		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->bind('export.slides', __CLASS__.'::doIt', $plugin);
		return true;
	}
}
