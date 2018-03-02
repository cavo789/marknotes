<?php
/**
 * Process <!--%META_DATA%--> and <!--%FAVICON%--> that can be
 * added in the template file (see /templates/html.php f.i.) and
 * replace these tags by content of meta.txt or favicon.txt
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Metadata extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.metadata';
	protected static $json_options = 'plugins.options.page.html.metadata';

	public static function doIt(&$html = null) : bool
	{
		if (trim($html) === '') {
			return true;
		}

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$title = '';

		// Get the filename being processed
		$md = $aeSession->get('filename', '');
		if ($md !== '') {
			// Get the absolute path
			$md = $aeSettings->getFolderDocs().$md;
			$md = $aeFiles->removeExtension($md).'.md';

			if ($aeFiles->exists($md)) {
				$content = $aeFiles->getContent($md);

				$aeMarkdown = \MarkNotes\Filetype\Markdown::getInstance();

				$title = $aeMarkdown->getHeadingText($content, '#');
			}
		}

		$arrOptions = self::getOptions('key', array());

		// Get the root folder
		$root = $aeSettings->getFolderWebRoot();

		foreach ($arrOptions as $key => $value) {
			// Verify if the pattern can be found in the html

			if (stripos($html, $value['pattern'])!==false) {
				if ($aeFiles->exists($filename = $root.$value['filename'])) {
					// Read the meta file and inject
					// its content in the HTML
					$content = trim($aeFiles->getContent($filename));

					// Now add note's content
					$content = str_replace('%TITLE%', $title, $content);
					$content = str_replace('%CONTENT%', $html, $content);

					// Replace some variables
					$aeFunctions = \MarkNotes\Functions::getInstance();

					$root = rtrim($aeFunctions->getCurrentURL(), '/');
					$content = str_replace('%ROOT%', $root, $content);

					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$content="<!-- Lines below are added by ".__METHOD__.
							" line ".__LINE__."-->\n".
							"<!-- Content of ".$filename."-->\n".
							$content."\n".
							"<!-- End for ".__METHOD__."-->";
					}
					/*<!-- endbuild -->*/
					$html = str_replace($value['pattern'], $content, $html);
				}
			} // if (strpos($html,
		} // foreach

		return true;
	}

	/**
	 * Provide additionnal css
	 */
	public static function addCSS(&$css = null) : bool
	{
		return true;
	}

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		return true;
	}

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// This plugin is only needed when at least one tag is mentionned
			$arrOptions = self::getOptions('key', array());
			$bCanRun = (count($arrOptions) > 0);
		}

		return $bCanRun;
	}

}
