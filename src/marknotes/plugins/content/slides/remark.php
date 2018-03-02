<?php

namespace MarkNotes\Plugins\Content\Slides;

defined('_MARKNOTES') or die('No direct access allowed');

class Remark extends \MarkNotes\Plugins\Content\Slides\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.remark';
	protected static $json_options = 'plugins.options.page.html.remark';

	private static $layout = 'remark';

	/**
	 *
	 */
	public static function doIt(array &$params = array()) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if ($params['filename'] !== "") {
			$fullname = $aeSettings->getFolderDocs(true).$params['filename'];

			if (!$aeFiles->exists($fullname)) {
				$fullname = utf8_decode($fullname);
			}

			if (!$aeFiles->exists($fullname)) {
				$aeFunctions->fileNotFound($fullname);
			}

			// Read the markdown file
			$aeMarkDown = \MarkNotes\FileType\Markdown::getInstance();

			$markdown = $aeMarkDown->read($fullname, $params);

			// Try to retrieve the heading 1
			$pageTitle = $aeMarkDown->getHeadingText($markdown, '#');

			// The slideshow functionnality will be remark

			// Consider that every headings (except h1)
			// should start in a new slide
			// The "remark" library allow indeed to give a
			// name to each slide by just adding "name: NAME"
			// in the markdown string

			$arrHeading = array('##','###','####','#####','######');
			foreach ($arrHeading as $head) {
				$matches = array();

				preg_match_all("/\\n".$head." (.*)/", $markdown, $matches);

				if (count($matches) > 0) {
					// Process one by one
					$j = count($matches[0]);

					for ($i = 0; $i < $j; $i++) {
						// $matches[0][$i] is f.i. "## TITLE"
						// while $matches[1][$i] is "TITLE"
						//
						// remark allow to specify the name of
						// the slide so add a "name:" property
						// in the markdown like this :
						//
						//   name: TITLE
						//   ---
						//   ## TITLE

						$markdown = str_replace(
							$matches[0][$i],
							//"???".PHP_EOL.str_replace('/',DS,$filename).PHP_EOL.
							// Add speaker note : ??? followed
							// by a line and the text
							"---".PHP_EOL.
							"name: ".rtrim($matches[1][$i], " #").PHP_EOL.
							// Be sure to not have a title
							// like ## Heading2 ## (==> remove
							// final # and space if there are
							// ones)
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
			$slideshow = $aeFiles->getContent($aeSettings->getTemplateFile('remark'));

			$html = str_replace('%CONTENT%', strip_tags($markdown), $slideshow);
			$html = str_replace('%SITE_NAME%', $aeSettings->getSiteName(), $html);
			$html = str_replace('%ROOT%', rtrim($aeFunctions->getCurrentURL(), '/'), $html);

			if (strpos($html, '%NOTE_TITLE%') !== false) {
				$html = str_replace('%NOTE_TITLE%', $pageTitle, $html);
			}

		} // if ($params['filename'] !== "")

		// And return the HTML to the caller
		$params['html'] = $html;
		return true;
	}
}
