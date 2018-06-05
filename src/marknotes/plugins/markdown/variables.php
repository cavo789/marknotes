<?php
/**
  * Allow to put own variables in the .md files like f.i. %AUTHOR%,
  * %VERSION%, ...
  * Note that there is no obligation to put the variable between %,
  * we can use your own or even no prefix, just a word like f.i.
  * "Joomla", "GitHub", ...
  *
  * The value can contains code for another plugin f.i. :fa-joomla:,
  * :fa-github:, :sun: (for emoji), ...
  *
  * These variables can be set in the settings.json file like, f.i., :
  *
  *		"plugins": {
  *			"options": {
  *				"markdown": {
  *					"variables": {
  *						"var": [{
  *								"pattern": "%AUTHOR%",
  *								"value": "Christophe"
  *							}, {
  *								"pattern": "%VERSION%",
  *								"value": "1.0"
  *							}, {
  * 							"pattern": "Joomla",
  *								"value": "Joomla :fa-joomla:"
  *							}]
  *						}
  *					}
  *				}
  *			}
  *		}
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Variables extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.variables';
	protected static $json_options = 'plugins.options.markdown.variables';

	/**
	 * Read the plugins.options.markdown.variables.var key of the
	 * settings.json file and replace variables in the markdown
	 * content.
	 */
	private static function replaceUserVariables(array &$params = null) : string
	{
		// The variable plugin should avoid to process keywords
		// in these tags :
		$arrNotIn = self::getOptions('not_in_tags', array('a','abbr','pre'));

		// Get the plugin options, get the list of variables
		// Stored in $json_options
		$arr = self::getOptions('var', array());

		$markdown = $params['markdown'];

		if (count($arr)>0) {
			$aeSession = \MarkNotes\Session::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			foreach ($arr as $variables) {
				if (trim($word = $variables['pattern']) !== '') {
					$pattern = $variables['pattern'];

					/** The list of variables can be a single word
					  * like "%AUTHOR%" and will be replaced by the
					  * name of the author like "AVONTURE Christophe"
					  * or can be something a little more complex :
					  * a regex.
					  *
					  * The pattern can be :
					  * %SEE_SOURCE%{filename=(.*)}
					  * which means "%SEE_SOURCE%" immediatly
					  * followed by
					  * {filename=xxxxxxxxxx} where xxxxx is a word
					  *
					  * The (.*) will capture this information so
					  * we've a variable => $1
					  *
					  * And that variable will be used in the
					  * replaced by value like in
					  * "([see source file](%URL%$1))"
					  */

					$tmp = $markdown;

					// Don't process words between one or more `
					// In markdown a `...` is a <pre> and ```....```
					// is a <code>
					//$tmp = preg_replace('/`{1,}[^`]*`{1,}/m', '', $tmp);

					if ($arrNotIn !== array()) {
						foreach ($arrNotIn as $tag) {
							$tmp = preg_replace('#<'.$tag.'.*?>.*?</'.$tag.'>#i', '', $tmp);
						}
					}

					if (preg_match_all('/(.*)('.$pattern.')(.*)/', $tmp, $matches)) {

						for ($k=0; $k < count($matches[0]); $k++) {

							$value = $variables['value'];

							for ($i = 2; $i < count($matches); $i++) {
								// Get what was in the file
								// For instance
								// "%SEE_SOURCE%{filename=a_file.md}"

								$tag = $matches[$i][$k];

								for ($j = 3; $j < count($matches); $j++) {
									if (isset($matches[$j][$k])) {
										$value = str_replace('$'.($j-2), $matches[$j][$k], $value);
									}
								}
							}

							// Replace the variable in his context :
							// in the matched line
							$value = str_replace($matches[2][$k], $value, $matches[0][$k]);

							// Then replace the line in the full text
							$markdown = str_replace($matches[0][$k], $value, $markdown);

						} // for ($k=0; $k < count($matches[0]); $k++)
					}
				}
			}

			/*<!-- build:debug -->*/
			$aeSettings = \MarkNotes\Settings::getInstance();
			if ($aeSettings->getDebugMode()) {
				if ($markdown!==$params['markdown']) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log(__METHOD__." has replace a few variables by their values", "info", 3);
				}
			}
			/*<!-- endbuild -->*/
		}

		return $markdown;
	}

	/**
	 * Replace variables like '%ROOT%', '%URL%', ... in the
	 * content but not when inside specific tags (like <a>, <pre>, ...)
	 * (specified in the $arrNotIn array)
	 */
	private static function replaceVar(string $var, string $value, string $markdown, array $arrNotIn = array()) : string
	{
		// Remove any <a>, <abbr>,  ... from the content
		// Don't keep tag and content, just remove it before the
		// process
		// The variable plugin should avoid to process keywords in
		// these tags :
		if ($arrNotIn == array()) {
			$arrNotIn = self::getOptions('not_in_tags', array('a','abbr','code','pre'));
		}

		$aeRegex = \MarkNotes\Helpers\Regex::getInstance();

		// When $markdown is a ... markdown string, remove the code
		// block
		$tmp = $aeRegex->removeMarkdownCodeBlock($markdown);

		// And, it's pretty rare but ... when $markdown is a HTML
		// string, remove a few tags
		//$tmp = $aeRegex->removeTags($tmp, $arrNotIn);

		// Don't process the variable if between one single `
		// (a <pre> block). Using a negative lookahead to check if,
		// just after the variable there is a ` character.
		// If yes, don't replace (since we've a pre block)
		// @URL : https://stackoverflow.com/a/6256377

		$pattern = "/(.*)(".$var.")([^`\n\r]*)(?!`)/m";
		if (preg_match_all($pattern, $tmp, $matches)) {
			for ($i=0; $i < count($matches[0]); $i++) {
				$match = str_replace($matches[2][$i], $value, $matches[0][$i]);
				$markdown = str_replace($matches[0][$i], $match, $markdown);
			}
		}

		return $markdown;
	}

	/**
	 * Replace intern variables like %URL%, %ROOT%, ...
	 */
	private static function replaceInternVariables(array &$params = null) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeLanguage = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// markdown content
		$markdown = $params['markdown'];

		// Get the web root like http://localhost/notes/
		$sRoot = rtrim($aeFunctions->getCurrentURL(), '/').'/';

		// Processing filename
		// f.i. c:\sites\notes\docs\marknotes\french\userguide.md
		$filename = str_replace('/', DS, urldecode($params['filename']));

		// Get the relative folder; like docs/marknotes/french/
		$sFolder = str_replace($aeSettings->getFolderDocs(true), '', dirname($filename).DS);

		// Get the relative folder like docs/marknotes/french/
		$sFolder = str_replace(DS, '/', $aeSettings->getFolderDocs(false).$sFolder);

		$markdown = self::replaceVar('%ROOT%', $sRoot, $markdown, array('abbr','code','pre'));

		if (strpos($markdown, '%PLUGINS%')!==false) {
			$aeEvents = \MarkNotes\Events::getInstance();
			$arr = $aeEvents->getEvents();
			$tmp = print_r($arr, true);
			$markdown = self::replaceVar('%PLUGINS%', "```\n".$tmp."\n```\n", $markdown);
		}

		// The %URL% variable should be relative to the note
		// and not from the master note i.e. where the %INCLUDE% tag
		// has been put
		//
		// domPDF requires URL not filename
		$task = $aeSession->get('task');

		if (!in_array($task, array('task.export.epub','task.export.docx'))) {
			// This is an hyperlink
			$url = str_replace(' ', '%20', $sRoot.$sFolder);
		} else {
			// When exporting the note, should be a local file
			// Escape the directory separator (since under Windows,
			// it's a backslash \ which has special meaning in a regex)
			$url = str_replace(DS, '\\'.DS, rtrim(dirname($filename)).DS);
		}

		$markdown = self::replaceVar('%URL%', $url, $markdown, array('abbr','code','pre'));

		if (strpos($markdown, '%NOTE_FOLDER%')!==false) {
			$tmp = rtrim($aeSettings->getFolderWebRoot().$sFolder, DS);
			$tmp = str_replace('/', DS, $tmp);
			$markdown = self::replaceVar('%NOTE_FOLDER%', rtrim($tmp, DS).DS, $markdown);
		} // if (strpos($markdown, '%NOTE_FOLDER%')!==FALSE)

		if (strpos($markdown, '%WEB_FOLDER%')!==false) {
			$tmp = rtrim($aeSettings->getFolderWebRoot(), DS);
			$tmp = str_replace('/', DS, $tmp);
			$markdown = self::replaceVar('%WEB_FOLDER%', rtrim($tmp, DS).DS, $markdown);
		} // if (strpos($markdown, '%NOTE_FOLDER%')!==FALSE)

		// ---------------------------------------------------------------
		// Based on the user's settings (settings.json)
		//
		//
		// ---------------------------------------------------------------

		// ---------------------------------------------------------------
		// Based on the user's settings (settings.json)
		//
		$tmp = $aeSettings->getFolderDocs(false);
		$tmp = str_replace('/', DS, $tmp);

		$markdown = self::replaceVar('%DOCS%', rtrim($tmp, DS).'/', $markdown);

		$markdown = self::replaceVar('%DEBUG%', $aeSettings->getDebugMode(), $markdown);
		$markdown = self::replaceVar('%LANGUAGE%', $aeSettings->getLanguage(), $markdown);
		$markdown = self::replaceVar('%SITE_NAME%', $aeSettings->getSiteName(), $markdown);
		//
		// ---------------------------------------------------------------

		if (strpos($markdown, '%NOTE_TITLE%') !== false) {
			$aeMarkDown = \MarkNotes\FileType\Markdown::getInstance();

			$mdContent = $aeFiles->getContent($params['filename']);
			if (trim($mdContent) == '') {
				$mdContent = $aeFiles->getContent(utf8_decode($params['filename']));
			}

			// Try to retrieve the heading 1
			$pageTitle = $aeMarkDown->getHeadingText($mdContent, '#');

			$markdown = self::replaceVar('%NOTE_TITLE%', $pageTitle, $markdown);
		}

		// ---------------------------------------------------------------
		// Variables using the filename i.e. the note being reading
		//
		if (isset($params['filename'])) {
			// Get the filename; without the extension, but with the path
			// (f.i. /subfolder/note (and not note.md))
			$relname = str_replace(DS, '/', $params['filename']);
			$relname = $aeFiles->removeExtension($relname);

			// Relative => remove the parent folder
			$relname = str_replace('/', DS, $aeFiles->removeExtension($relname));
			$relname = str_replace($aeSettings->getFolderDocs(true), '', $relname);

			// Finally, use Unix / URL style, no Windows folder sep.
			$relname = str_replace(DS, '/', $relname);

			$markdown = self::replaceVar('%FILENAME%', $relname, $markdown);

			// Just the name of the file ("note") without the extension
			$basename = basename($params['filename']);
			$basename = $aeFiles->removeExtension($basename);
			$markdown = self::replaceVar('%BASENAME%', $basename, $markdown);

			$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

			$urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($relname, 'html'));

			$markdown = self::replaceVar('%VERSION_SLIDESHOW%', basename($aeFiles->replaceExtension($urlHTML, 'reveal')), $markdown);

			$markdown = self::replaceVar('%VERSION_SLIDESHOW_TITLE%', $aeSettings->getText('action_slideshow', 'View this article like a slideshow'), $markdown);

			$markdown = self::replaceVar('%VERSION_PDF%', basename($aeFiles->replaceExtension($urlHTML, 'pdf')), $markdown);

			$markdown = self::replaceVar('%VERSION_PDF_TITLE%', $aeSettings->getText('export_pdf', 'Export the note as a PDF document'), $markdown);

			$markdown = self::replaceVar('%VERSION_HTML%', basename($aeFiles->replaceExtension($urlHTML, 'html')), $markdown);

			$markdown = self::replaceVar('%VERSION_HTML_TITLE%', $aeSettings->getText('action_html', 'View this slideshow like an article'), $markdown);

			$markdown = self::replaceVar('%URL_PAGE%', $urlHTML, $markdown);
		}
		//
		// ---------------------------------------------------------------

		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			if ($markdown!==$params['markdown']) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log(__METHOD__." has replace a few variables by their values", "info", 3);
			}
		}
		/*<!-- endbuild -->*/

		return $markdown;
	}

	/**
	 * The markdown file has been read, this function will get the content of the .md file and
	 * make some processing like data cleansing
	 *
	 * $params is a associative array with, as entries,
	 *		* markdown : the markdown string (content of the file)
	 *		* filename : the absolute filename on disk
	 */
	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		// Important : first the user variables since
		// we can reuse there intern variables like f.i. %URL%

		$params['markdown'] = self::replaceUserVariables($params);
		$params['markdown'] = self::replaceInternVariables($params);

		return true;
	}
}
