<?php
/**
 * Analyze the markdown content; once read, and replace any html img tag by the
 * correct markdown syntax.
 *
 * So replace <img src="image1.png" width="133" height="24" /> by
 * ![](image1.png "133x24").
 *
 * "133x24" take the place of the title attribute (standard syntax in Markdown).
 * This because Marknotes will retrieve this "title" and detect that it's
 * a width x height and, therefore, will give a size to the image
 */
namespace MarkNotes\Plugins\Markdown\Beautify_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class Images
{
	public function doIt(array $params) : string
	{
		$pattern = '/\<img src=[\'"]([^\'"]*)[\'"] width=[\'"]([^\'"]*)[\'"] height=[\'"]([^\'"]*)[\'"] \/\>/';

		$markdown = $params['markdown'];

		if (preg_match_all($pattern, $markdown, $matches)) {
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSession = \MarkNotes\Session::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$i=0;

			for ($i=0; $i<count($matches[0]); $i++) {
				// $tag    => $matches[0][0] will be f.i.
				//      "<img src="image1.png" width="133" height="24" />"
				// $img    => $matches[1][0] will be f.i. "image1.png"
				// $width  => $matches[2][0] will be f.i. "133"
				// $height => $matches[3][0] will be f.i. "24"

				list($tag, $img, $width, $height) = $matches;

				$img = '![]('.$img[$i].' "'.$width[$i].'x'.$height[$i].'")';

				$markdown=str_replace($tag[$i], $img, $markdown);
			} // for

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
