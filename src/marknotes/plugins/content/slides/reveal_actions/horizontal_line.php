<?php
/**
 * Reveal - Consider an <hr> (can be <hr   >, <hr  />, ...) as a new slide
 * There is no configuration for this action
 */
namespace MarkNotes\Plugins\Content\Slides\Reveal_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class Horizontal_Line
{
	public function doIt(string $html) : string
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$html = trim($html);

		if ($html==='') {
			return '';
		}

		// Replace every <hr/>, <hr    />, ... (with or without ending /)
		// by a slide i.e. a section
		if (preg_match_all('/<hr *\/?>/', $html, $matches)) {
			foreach ($matches[0] as $tmp) {
				$html = str_replace($tmp, '</section>'.PHP_EOL.PHP_EOL.'<section>', $html);
			}
			if (substr($html, 0, strlen('</section>')) == '</section>') {
				$html = substr($html, strlen('</section>'), strlen($html));
			}

			// Be sure to have an ending </section>
			if (!$aeFunctions->endsWith($html, '</section>')) {
				$html .= '</section>';
			}
		}

		return $html;
	}
}
