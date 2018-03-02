<?php
namespace MarkNotes\Plugins\Content\Slides;

defined('_MARKNOTES') or die('No direct access allowed');

class Reveal extends \MarkNotes\Plugins\Content\Slides\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.reveal';
	protected static $json_options = 'plugins.options.page.html.reveal';

	private static $layout = 'reveal';

	/**
	 * Replace a slide with only an image (like below illustrated)
	 * by a section with a background image
	 *	---
	 *	![](.images/image.jpg)
	 */
	private static function insertSlideBackgroundImage(string $markdown) : string
	{

		// A manual section break (i.e. a new slide) can be
		// manually created in marknotes by just creating,
		// in the note a new line with --- (or -----).
		// Only these characters on the beginning of the line.
		//
		// WARNING : the string should be with LF (linefeed)
		// only and not CRLF

		$newSlide = '\n+^-{3,5}$\n+';
		$imgTag = '\!\[.*\]\((.*)\)$\n';

		$matches = array();
		if (preg_match_all('/'.$newSlide.$imgTag.'/m', $markdown, $matches)) {
			$j = count($matches[0]);
			for ($i = 0; $i <= $j; $i++) {
				if (isset($matches[0][$i])) {
					$markdown = str_replace($matches[0][$i], PHP_EOL.PHP_EOL.'###### @@@@'.base64_encode($matches[1][$i]).PHP_EOL.PHP_EOL, $markdown);
				}
			}
		}

		return $markdown;
	}

	private static function processMarkDown(string $markdown) : string
	{
		$markdown = self::insertSlideBackgroundImage($markdown);
		return $markdown;
	}

	private static function processHTML(string $html) : string
	{
		// In order to keep this file has light as possible, use
		// external actions
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Look in the reveal_actions subfolder and take every .php
		// file
		$folder = __DIR__.'/reveal_actions/';
		$arrActions = $aeFiles->rglob($pattern = '*.php', $path = $folder);

		// Convention : if the filename start with an underscore
		// (like in _addThings.php)
		// consider that file as disabled so don't call it
		for ($i=count($arrActions); $i>0; $i--) {
			$action = $arrActions[$i-1];
			if (substr(basename($action), 0, 1) === '_') {
				unset($arrActions[$i-1]);
			}
		}

		// Run each actions sequentially
		if (count($arrActions)>0) {
			foreach ($arrActions as $action) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("	Load [".$action."]", "debug");
				}
				/*<!-- endbuild -->*/

				try {
					require_once($action);

					// If the file is "add_slides.php", the class
					// will be "add_slides"
					$class = $aeFiles->removeExtension(basename($action));

					$class = "\\MarkNotes\\Plugins\\Content\\Slides\\Reveal_Actions\\".$class;

					$class = new $class();
					$html = $class->doIt($html);
					unset($class);
				} catch (Exception $e) {
					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log($e->getMessage(), 'error');
					}
					/*<!-- endbuild -->*/
				}
			}
		}

		return $html;
	}

	/**
	 * Build the reveal html presentation
	 */
	public static function doIt(array &$params = array()) : bool
	{
		$html = '';

		$content = $params['html'];

		// Get the 'no_html_convert' option i.e. should we first
		// convert the .md into HTML before running Reveal ?
		$no_html_convert = boolval(self::getOptions('no_html_convert', 0));

		if (!$no_html_convert) {
			// It's a HTML content, run every actions
			$content = self::processHTML($content);
		} else {
			// It's a markdown content
			// Automatically add a horizontal slide break between
			// each titles
			$content = preg_replace("/^(#{2,})/m", "\n---\n\n$1", $content);

			// No empty slides
			$content = preg_replace("/^(-{2,3})\n*-{2,3}\n*/m", "$1\n\n", $content);
			$content =
				'<section data-markdown="" data-separator="^\r?\n*---\r?\n*$" '.
				'data-separator-vertical="^\r?\n--\r?\n$">'.
					'<script type="text/template">'.$content.'</script>'.
				'</section>';
		}

		// And return the HTML to the caller
		$params['html'] = $content;

		return true;
	}
}
