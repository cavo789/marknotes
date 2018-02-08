<?php
/**
 * Working on big files isn't always the most efficient way.
 * This plugin will allow to include files in the markdown
 * content "just like" if the content was in a single file.
 *
 * For instance :
 *
 *	# My big story
 *
 *	%INCLUDE .chapters/settings.md{"once":1}%
 *	%INCLUDE .chapters/chapter1.md%
 *	%INCLUDE .chapters/chapter2.md%
 *	%INCLUDE .chapters/chapter3.md%
 *
 * After the filename, settings can be given in a json
 * format like {"once":1}
 *
 *	once:1	=> that file will be loaded only once even when
 *				 	multiples .md files are referencing the same
 *					include.
 *					Usefull for f.i. including a settings file.
 *					once=1 is good when you're including a settings
 *					file (i.e a markdown file where you're defining,
 *					once and for all, your abbreviations, URLs, ...),
 *					once=0 can be good when you wish to include
 *					headers and footers f.i.
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Include_File extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.include';
	protected static $json_options = 'plugins.options.markdown.include';

	private static function getURL(string $filename) : string
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the web root like http://localhost/notes/
		$sRoot = rtrim($aeFunctions->getCurrentURL(), '/').'/';

		// Add the /docs folder in the URL (so something like
		// http://localhost:8080/notes/docs/)
		$url = $sRoot.rtrim($aeSettings->getFolderDocs(false), DS).'/';

		// Get the relative folder of the notes (from /docs)
		// Something like (/marknotes/userguide)
		$path=str_replace($aeSettings->getFolderDocs(true), '', dirname($filename));

		$url.=rtrim(str_replace(DS, '/', $path), '/').'/';

		// And return the url
		// http://localhost:8080/notes/docs/marknotes/userguide)
		return str_replace(' ', '%20', $url);
	}

	/**
	 * When we're processing a file that contains a H1, the problem
	 * is that the master file probably already contains a H1 so we've
	 * two. This is not a good habit so, for each included files, if
	 * we've a H1 in the included content, scan every headings in that
	 * content and increment by one
	 */
	private static function IncrementHeadings(string $sContent) : string
	{
		$increment = boolval(self::getOptions('increment_headings', 0));
		if (preg_match_all('/^(#{1,} )(.*)/m', $sContent, $matches)) {
			list($tag, $heading, $title) = $matches;

			for ($i = 0; $i < count($heading); $i++) {
				$sContent = str_replace($tag[$i], '#'.$heading[$i].$title[$i], $sContent);
			}
		}

		return $sContent;
	}

	private static function processIncludes(string $markdown, string $filename, string $indent = '') : string
	{
		static $arrLoaded=null;

		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
		}
		/*<!-- endbuild -->*/

		// Replace the variable by the location of the current note
		if (strpos($markdown, '%NOTE_FOLDER%') !== false) {
			$folder=rtrim(str_replace('/', DS, dirname($filename)), DS).DS;
			$markdown = str_replace('%NOTE_FOLDER%', $folder, $markdown);
		}

		// The %INCLUDE ...% tag should start the line. If there is
		// one or more character before (for instance a single space)
		// consider the tag not active.
		// So " %INCLUDE ..." won't work and can be therefore use to
		// temporary disable the tag f.i.
		//
		// The include statement is something like :
		//
		// %INCLUDE %NOTE_FOLDER%.settings/0_links.md{"once":1}%
		//
		// The JSON part ("  {"once":1}  " in this example) is
		// optionnal and, if mentionned, contains settings

		if ($arrLoaded===null) {
			$arrLoaded=array();
		}

		// Retrieve every occurences of %INCLUDE filename%
		if (preg_match_all('/^([ \\t])*%INCLUDE ([^{\\n]*)({.*})?%/m', $markdown, $matches)) {
			$aeSession = \MarkNotes\Session::getInstance();
			$task = $aeSession->get('task');

			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSession = \MarkNotes\Session::getInstance();

			// Retrieve the note fullpath.
			$root = dirname($filename).DS;

			// Loop and process every %INCLUDE ..% tags
			for ($i=0; $i<count($matches[0]); $i++) {
				// $tag	=> $matches[0][0] will be f.i.
				// 		%INCLUDE .chapters/chapter1.md%"
				// $before => $matches[1][0] will be f.i.
				//		"  " i.e. what's before %INCLUDE
				// $file	=> $matches[2][0] will be f.i.
				//		".chapters/chapter1.md"
				// $json	=> $matches[3][0] will be f.i.
				//		"{"once":1}"
				list($tag, $before, $file, $json) = $matches;

				// %INCLUDE filename.md% => when no path has been
				// modified, it means that the file is in the same
				// folder of the processed note i.e. $root
				if (!$aeFiles->exists($file[$i])) {

					$file[$i]=str_replace('/', DS, $file[$i]);
					if (!$aeFunctions->startsWith($file[$i], $root)) {
						$file[$i]=$root.$file[$i];
					}
				}

				// Get the filename to include
				$sFile = str_replace('/', DS, $file[$i]);

				if ($sFile=='') {
					// The file doensn't exists
					continue;
				}

				if ($before[$i]=='') {
					// Ok, no space before the tag => it's enabled => run it
					$bContinue=1;

					if ($aeFiles->exists($sFile)) {
						if ($json[$i]!=="") {
							$tmp=json_decode($json[$i], true);

							$once=$tmp['once']??0;
							if (boolval($once)) {
								// The file should be included only once
								// So if already processed previously, don't import again.
								if (in_array($sFile, $arrLoaded)) {
									$bContinue=0;
								}
							} // if(boolval($once))
						} // if ($json[$i]!=="")

						if ($bContinue) {
							/*<!-- build:debug -->*/
							if ($aeSettings->getDebugMode()) {
								$aeDebug->log($indent.'Including '.$sFile, 'debug');
							}
							/*<!-- endbuild -->*/

							$arrLoaded[]=$sFile;

							// Read the file
							$sContent = trim($aeFiles->getContent($sFile));

							// We're including a file.
							// Headings will be incremented by one
							$sContent = self::IncrementHeadings($sContent);

							//$aeEvents = \MarkNotes\Events::getInstance();
							//$aeEvents->loadPlugins('markdown');
							//$params['markdown'] = $sContent;
							//$params['filename'] = $sFile;
							//$args = array(&$params);

							//$aeEvents->trigger('markdown::markdown.read', $args);

							//$sContent = $args[0]['markdown'];

							// Replace non breaking space to spaces since, otherwise,
							// rendering will fail and no output will be done for the
							// sentence/paragraph with that character.
							// A non breaking space is U+00A0 (Unicode) but encoded
							// as C2A0 in UTF-8
							// Replace by a space character
							$sContent=preg_replace('/\x{00a0}/siu', ' ', $sContent);

							if (strpos($sContent, '%URL%') !== false) {
								// The %URL% variable should be relative to this note ($sFile)
								// and not from the master note i.e. where the %INCLUDE% tag
								// has been put

								if (!in_array($task, array('task.export.epub','task.export.docx','task.export.pdf'))) {
									// This is an hyperlink
									$sContent = str_replace('%URL%', str_replace(' ', '%20', self::getURL($sFile)), $sContent);
								} else {
									// When exporting the note, should be a local file
									// Escape the directory separator (since under Windows,
									// it's a backslash \ which has special meaning in a regex)
									$sContent = str_replace('%URL%', str_replace(DS, '\\'.DS, rtrim(dirname($sFile)).DS), $sContent);
								}
							}

							// Now, perhaps that note (the included one) also contains
							// %INCLUDE ...% tag so ... process it again

							while (strpos($sContent, '%INCLUDE ') !== false) {
								$sContent = self::processIncludes($sContent, $sFile, $indent.'	');
							}
						} else {
							$sContent = '';
						} // if ($bContinue)

						$markdown = str_replace($tag[$i], $sContent, $markdown);
					} else { // if ($aeFiles->exists($sFile))
						/*<!-- build:debug -->*/
						if ($aeSettings->getDebugMode()) {
							$aeDebug->log('	Failure : file ['.$sFile.'] not found ! If the path is relative, think to add %NOTE_FOLDER% in your call so the file will be correctly retrieved (f.i. %INCLUDE %NOTE_FOLDER%file-to-include.md%)', 'error');
						}
						/*<!-- endbuild -->*/
					}// if ($aeFiles->exists($sFile))
				} else { // if ($before[$i]=='')
					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug->log($indent.' - Not processed since the tag isn\'t at the begining of the sentence. If there are characters before the %INCLUDE% tag, the tag is ignored', 'debug');
						$aeDebug->log($indent.' ***'.$tag[$i].'***', 'debug');
					}
					/*<!-- endbuild -->*/

					// there is at least one space before the tag => it's not active

					if (!in_array($task, array('task.export.html','main','task.export.md'))) {
						// Don't show the tag, if disabled, for f.i. html / pdf / ... output
						$markdown = str_replace($tag[$i], '', $markdown);
					} else {
						// **********************************************************
						// The tag "%INCLUDE " (with a space after) CAN'T stay unchanged
						// due to the recursive call of this function. The tag should be changed
						// to something else otherwise we'll have an infinite loop
						// **********************************************************

						$markdown = str_replace($tag[$i], str_replace('%INCLUDE', '%INCLUDE_disabled', $tag[$i]), $markdown);
					}
				} // if ($before[$i]=='')
			} // for
		} // preg_match_all

		return $markdown;
	}

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		if (!(preg_match('/^([ \\t])*%INCLUDE ([^{\\n]*)({.*})?%/m', $params['markdown'], $match))) {
			// No include tag found; return
			return true;
		}

		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// Remember this filename i.e. the "master" file.
		$filename = $aeSettings->getFolderDocs(true).$aeSession->get('filename');

		$params['markdown'] = self::processIncludes($params['markdown'], $filename, '');

		return true;
	}
}
