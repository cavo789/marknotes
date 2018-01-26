<?php
/**
 * Reveal - Process bullets - Add animation (i.e. "fragments" in term of Reveal)
 *
 *	 	"plugins" : {
 *			"options": {
 *				 "reveal": {
 *					 "animation": {
 *						 "bullet": "fragment"
 *					 }
 *				 }
 *			 }
 *		 }
 */
namespace MarkNotes\Plugins\Content\Slides\Reveal_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class Bullets
{
	private static $json_key = 'plugins.options.page.html.reveal';

	public function doIt(string $html) : string
	{
		$html = trim($html);

		if ($html==='') {
			return '';
		}

		$aeSettings = \MarkNotes\Settings::getInstance();
		$arrSettings = $aeSettings->getPlugins(static::$json_key);

		$style = $arrSettings['animation']['bullet'] ?? 'fragment';

		if ($style === 'fragment') {
			$matches = array();

			preg_match_all('/<li([^>])*>(.*)<\/li>/', $html, $matches);

			// Consider <li class="bullets">TEXT</li>
			//
			//	$pattern	will be the full match i.e.  <li class='bullets'>TEXT</li>
			//	$attrs 		will be the " class='bullets'"
			// 	$text 		will be the "TEXT"

			list($pattern, $attrs, $text) = $matches;

			$j = count($matches[0]);

			for ($i = 0; $i < $j; $i++) {
				$tmp = trim($text[$i]);

				$html = str_replace(
					$pattern[$i],
					'<li'.$attrs[$i].' class="fragment">'.$tmp.'</li>',
					$html
				);
			}
		} // if ($style === 'fragment')

		return $html;
	}
}
