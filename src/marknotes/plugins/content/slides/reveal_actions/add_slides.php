<?php
/**
 * Reveal - Add slides (one slide by heading) and add the
 * transitions as configured in settings.json
 *
 *	 	"plugins" : {
 *			"options": {
 *				 "reveal": {
 *					 "animation": {
 *						 "h1": "zoom",
 *						 "h2": "random",
 *						 "h3": "random",
 *						 "h4": "slide-in",
 *						 "h5": "fade",
 *						 "h6": "convex"
 *					 }
 *				 }
 *			 }
 *		 }
 */
namespace MarkNotes\Plugins\Content\Slides\Reveal_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class Add_Slides
{
	private static $json_key = 'plugins.options.page.html.reveal';

	/**
	 * Add vertical slides : get the list of all h2 tags
	 * The regex below will match any line (get the full line)
	 * where there is a <h2> tag
	 *
	 *  For instance :
	 * 		<section id="my-title"><h2>My Title</h2>
	 *
	 * Add an empty <section> just before and a </section>
	 * before the next <h2> :
	 *
	 *		<section>
	 * 			<section id="my-title"><h2>My Title</h2>
	 *		  	... a lot of things ...
	 *		</section>
	 *		<section>
	 * 			<section id="my-second"><h2>My second/h2>
	 *
	 * So, except for the first found h2 tag, we'll always
	 * add </section><section> to put any h2 content in a
	 * vertical slide
	*/
	private function addVerticalSlides(string $html) : string
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$arrSettings = $aeSettings->getPlugins(static::$json_key);

		$bAdd = boolval($arrSettings['addVerticalSlides']??0);

		if ($bAdd) {
			// Ok, add vertical slides for each h2 found
			// Everything between two h2 will be vertical
			// so to go from one h2 to the other one, just use
			// the right arrow.

			$regex = '/.*<\s*h2[^>]*>.*?<\s*\/\s*h2>.*/';
			$matches = array();
			preg_match_all($regex, $html, $matches);

			$add_section  = '<section class="vertical">';

			if (count($matches)) {
				foreach ($matches[0] as $match) {
					$html = str_replace($match, $add_section.$match, 	$html);
					$add_section = '</section><section class="vertical">';
				}
				$html .= '</section>';
			}
		}
		return $html;
	}

	public function doIt(string $html) : string
	{
		$html = trim($html);

		if ($html==='') {
			return '';
		}

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$arrSettings = $aeSettings->getPlugins(static::$json_key);

		// Add a data-transition based on the heading : zoom for h1, concave for h2, ...
		// Every heading will be put in a section (i.e. a slide)

		$matches = array();
		preg_match_all('|<h[^>]+>(.*)</h[^>]+>|iU', $html, $matches);

		// Retrieve the animations between slides in the settings.json
		$arr = array(
			'h1' => 'zoom',
			'h2' => 'concave',
			'h3' => 'slide-in',
			'h4' => 'fade',
			'h5' => 'fade',
			'h6' => 'fade');

		// Transitions supported by reveal.js
		$arrTransitions = array('slide', 'none', 'fade','slide','convex','concave','zoom');

		// $matches contains the list of titles (including the tag so f.i. "<h2>Title</h2>"
		foreach ($matches[0] as $tmp) {
			// The tag (like h2)
			$head = substr($tmp, 1, 2);

			// Retrieve the animation between slides (sections)
			$transition = $arrSettings['animation'][$head] ?? $transition;

			if ($transition === 'random') {
				// Random => take one transition, randon
				$transition = $arrTransitions[array_rand($arrTransitions, 1)];
			}

			if (substr($tmp, 0, 8) === '<h6>@@@@') {
				// Very special tag : create a new section with an image background

				$extraAttributes = $arrSettings['section']['extra_data_img_attr'] ?? '';

				// Add the slide background image
				$image = $extraAttributes.' data-background-image="'.base64_decode(str_replace('</h6>', '', str_replace('<h6>', '', $tmp))).'" ';

				$html = str_replace($tmp, '</section>'.PHP_EOL.PHP_EOL.'<section '.$image.' data-background-transition="'.$transition.'">', $html);
			} else {
				// In order to have nice URLs, extract the title (stored in $tmp)
				// $tmp is equal, f.i., to <h2>My slide title</h2>
				$id = $aeFunctions->slugify(strip_tags($tmp));

				// The ID can't start with a figure, remove it if any
				// Remove also . - , ; if present at the beginning of the id
				$id = preg_replace("/^[\d|.|\-|,|;]+/", "", $id);

				// No background
				// Put the "id" on the section and not, both,
				// on the section and the h1, h2, ... which is
				// an error (ids should be unique)
				$html = str_replace(
					$tmp,
					'</section>'.PHP_EOL.PHP_EOL.
					'<section id="'.$id.'" data-transition="'.$transition.'">'.
					'<'.$head.'>'.strip_tags($tmp).'</'.$head.'>',
					$html
				);
			} // if (substr($tmp, 0, 8)==='<h2>@@@@')
		} // foreach

		// Be sure there is no empty slide
		foreach ($arr as $animation) {
			$html = preg_replace('/<section data-transition="'.$animation.'">[\s\n\r]*<\/section>/m', '', $html);
		}

		$html = trim($html);

		// Be sure to have a correctly formatted html i.e. starting with <section>
		if ($aeFunctions->startsWith($html, '</section>')) {
			$html = trim(substr($html, strlen('</section>')));
		}

		// and ending by </section>
		if (!$aeFunctions->endsWith($html, '</section>')) {
			$html .= '</section>';
		}

		$html = self::addVerticalSlides($html);

		return $html;
	}
}
