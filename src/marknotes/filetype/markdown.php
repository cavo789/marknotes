<?php
/*
 * The markdown version will be put in the cache folder but
 * ONLY WHEN the note will not contains encrypted informations.
 * If it's the case, markdown can't be put in the cache otherwise
 * we'll store unencrypted informations which is a bad idea.
 */
namespace MarkNotes\FileType;

defined('_MARKNOTES') or die('No direct access allowed');

class Markdown
{
	protected static $hInstance = null;

	public function __construct()
	{
		return true;
	}

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Markdown();
		}
		return self::$hInstance;
	}

	/**
	* From a markdown content, return an heading text
	* (by default the ""# TEXT" i.e. the heading 1)
	*/
	public function getHeadingText(string $markdown, string $heading = '#') : string
	{
		// Try to find a heading 1 and if so use that text for the
		// title tag of the generated page
		$matches = array();
		$title = '';

		try {
			preg_match("/".$heading." ?(.*)/", $markdown, $matches);
			$title = (count($matches) > 0) ? trim($matches[1]) : '';

			// Be sure that the heading 1 wasn't type like
			// # MyHeadingOne #
			// i.e. with a final #

			$title = ltrim(rtrim($title, $heading), $heading);
		} catch (Exception $e) {
		}

		return $title;
	}

	/**
	* Convert any links like ![alt](image/file.png) or
	* <img src='image/file.php' /> to an absolute link to the image
	*/
	private static function setImagesAbsolute(string $markdown, array $params = null) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// List of tasks (extensions) for which images should be
		// referred locally i.e. not through a http:// syntax but
		// like c:\folder, local on the filesystem so the convertor
		// program can retrieve the file (the image)
		$arrFilePaths = array('doc','epub','pdf');

		$task = $aeSession->get('task');

		$folderNote = str_replace('/', DS, rtrim($aeSettings->getFolderDocs(true), DS).'/');

		if (isset($params['filename'])) {
			$params['filename'] = str_replace($folderNote, '', $params['filename']);

			$folderNote .= rtrim(dirname($params['filename']), DS).DS;

			$subfolder = trim(str_replace(basename($params['filename']), '', $params['filename']));

			// Get the full path to this note
			// $url will be, f.i., http://localhost/notes/docs/
			$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

			// Extract the subfolder f.i. private/home/dad/
			if ($subfolder !== '') {
				$url .= str_replace(DS, '/', $subfolder);
			}

			$pageURL = $url;

			if (in_array($task, $arrFilePaths)) {
				// PDF exportation : links to images should remains
				// relative
				$url = rtrim($aeSettings->getFolderDocs(true), DS).DS;
				if ($subfolder !== '') {
					$url .= $subfolder;
				}
			}

			// Don't allow spaces in name
			if (!in_array($task, $arrFilePaths)) {
				$url = str_replace(' ', '%20', $url);
			}

			$imgTag = '\!\[(.*)\]\((.*)\)';

			$matches = array();

			// When the task is DOCX, PDF, ... links to
			// images should be from the disk and not from an
			// url so replace absolute links by relative ones,
			// then, replace links by hard disk filepaths
			if (in_array($task, $arrFilePaths)) {
				if (preg_match_all('/'.$imgTag.'/', $markdown, $matches)) {
					for ($i = 0; $i < count($matches[2]); $i++) {
						$matches[2][$i] = str_replace($pageURL, '', $matches[2][$i]);
						$matches[2][$i] = str_replace(str_replace(' ', '%20', $pageURL), '', $matches[2][$i]);

						$markdown = str_replace($matches[0][$i], '!['.$matches[1][$i].']('.$matches[2][$i].')', $markdown);
					}
				}
			} // if(in_array($task, $arrFilePaths))

			// Get the list of images i.e. tags like :
			//
			// ![My image](.images/local.jpg)
			//
			// and check if the file is local (in a subfolder of the
			// note). If so, convert the relative
			//
			// ![My image](.images/local.jpg) to an absolute path
			// ![My image](http://localhost/folder/.images/local.jpg)
			$matches = array();
			if (preg_match_all('/'.$imgTag.'/', $markdown, $matches)) {
				$j = count($matches[0]);
				for ($i = 0; $i <= $j; $i++) {
					if (isset($matches[2][$i])) {
						// Add the fullpath only if the link to the
						// image doesn't contains yet an hyperlink
						if (strpos($matches[2][$i], '//') === false) {
							$filename = str_replace('/', DS, $matches[2][$i]);

							if (strpos($filename, $folderNote) === false) {
								$filename = $folderNote.$filename;
							}

							// Relative name to the image
							$img = $matches[2][$i];
							// If the image url doesn't start with
							// http, make the url absolute by adding
							// the full url of the note
							if (strpos($img, 'http')!== 0) {
								$img=$url.$img;
							}

							if (in_array($task, $arrFilePaths)) {
								$img = str_replace(str_replace(' ', '%20', $pageURL), '', $img);
								// convert the / to the OS directory
								// separator
								$img = str_replace('/', DS, $img);

								//$img = $url.$img;
								// If the link to the image contains
								// \. double the slash
								// (otherwise the slash will be
								// interpreted as an escape character)
								$img = str_replace('\.', '\\\.', $img);
							} // if($task==='pdf')

							if ($aeFiles->exists($filename)) {
								$markdown = str_replace($matches[0][$i], '!['.$matches[1][$i].']('.$img.')', $markdown);
							} else {
								/*<!-- build:debug -->*/
								/*if (in_array($task, array('task.export.html','main','html'))) {
									if ($aeSettings->getDebugMode()) {
										$aeDebug = \MarkNotes\Debug::getInstance();
										$aeDebug->here('DEBUG MODE --- In file	'.$params['filename'].' ==> '.$filename.' NOT FOUND');
									}
								}*/
								/*<!-- endbuild -->*/
							}
						}//if (strpos('//', $matches[2][$i])===FALSE)
					}
				}
			} // if (preg_match_all('/'.$imgTag.'/'

			// And process <img> tags
			$imgTag = '<img (.*)src *= *["\']([^"\']+["\']*)[\'|"]';

			$matches = array();
			if (preg_match_all('/'.$imgTag.'/', $markdown, $matches)) {
				$j = count($matches);
				for ($i = 0; $i <= $j; $i++) {
					// Derive the image fullname
					// $folderNote.str_replace('/',DS,$matches[1][$i]))
					// and check if the file exists
					if (isset($matches[2][$i])) {
						// Add the fullpath only if the link to the
						// image doesn't contains yet an hyperlink
						if (strpos($matches[2][$i], '//') === false) {
							// Relative name to the image
							$img = $matches[2][$i];

							if (in_array($task, $arrFilePaths)) {
								// PDF => convert the / to the OS
								// directory separator
								$img = str_replace('/', DS, $img);

								// If the link to the image contains
								// \. double the slash
								// (otherwise the slash will be
								// interpreted as
								// an escape character)
								$img = str_replace('\.', '\\\.', $img);
							} // if($task==='pdf')

							$filename = $folderNote.str_replace('/', DS, $matches[2][$i]);

							if ($aeFiles->exists($filename)) {
								$img = $url.trim($matches[2][$i]);
								$markdown = str_replace($matches[0][$i], '<img src="'.$img.'" '.$matches[1][$i], $markdown);
							}
						}
					}
				}
			} // if (preg_match_all('/'.$imgTag.'/'
		} // if (isset($params['filename']))

		return $markdown;
	}

	/**
	 * Get the content of the file. Use the cache system to
	 * speed up the retrieving.
	 */
	private static function doReadContent(string $filename, array $params) : string
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		if ($aeFiles->exists($filename)) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log("Process file ".$filename,"debug");
			}
			/*<!-- endbuild -->*/

			// remove empty lines if needed
			$markdown = trim($aeFiles->getContent($filename));

			// --------------------------------
			// Call content plugins
			$aeEvents->loadPlugins('markdown');

			$params['markdown'] = $markdown;
			$params['filename'] = $filename;

			$args = array(&$params);
			$aeEvents->trigger('markdown::markdown.read', $args);
			$markdown = $args[0]['markdown'];

			// Get the full path to this note
			$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
			$noteFolder = $url.str_replace(DS, '/', dirname($params['filename'])).'/';
			// --------------------------------

			// In the markdown file, two syntax are possible
			// for images, the ![]() one or the <img src one
			// Be sure to have the correct relative path i.e.
			// pointing to the folder of the note
			$task = $aeSession->get('task');
			if ($task!=='task.search.search') {
				$markdown = self::setImagesAbsolute($markdown, $params);
			}

			// And do it too for links to the files folder
			$markdown = str_replace('href=".files/', 'href="'.$noteFolder.'.files/', $markdown);
		} else {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('Error while opening '.$filename, 'error');
			}
			/*<!-- endbuild -->*/

			$markdown = '';
		}

		return $markdown;
	}

	/**
	* Read a markdown file and return its content.
	*
	* $params['encryption'] = 0 : encrypted data should
	*			be displayed unencrypted
	*						 1 : encrypted infos should
	*			stay encrypted
	*/
	public function read(string $filename, array $params = array()) : string
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
		}
		/*<!-- endbuild -->*/

		$aeSession = \MarkNotes\Session::getInstance();

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;
		$task = $aeSession->get('task');

		$arr=null;

		if ($bCache) {
			// The content isn't the same, depending on the task
			// key will be f.i.
			// "task.export.html###c:\notes\docs\a.md"
			$key = 'task.markdown.read###'.$filename;
			$aeCache = \MarkNotes\Cache::getInstance();
			$cached = $aeCache->getItem(md5($key));
			$arr = $cached->get();
		}

		if (is_null($arr)) {
			$arr = array();

			$arr['markdown'] = self::doReadContent($filename, $params);

			// Markdown support only H1 to H6; this is specified in
			// the CommonMark specification :
			// https://github.com/commonmark/CommonMark/blob/master/spec.txt#L783
			// Read "More than six `#` characters is not a heading"
			// So, the regex here below will replace 7 or more # to only 6
			// This once every markdown plugins have already be called
			$regex = '/^(#{7,}(.*))$/m';
			$arr['markdown'] = preg_replace($regex, '######$2', $arr['markdown']);

			if (trim($arr['markdown'])=='') {
				// Don't cache if the content is empty.
				$bCache = false;
			} elseif (strpos($arr['markdown'], ENCRYPT_MARKDOWN_TAG)>0) {
				// Check if the markdown contains the encrypt tag.
				// If yes, this means that this note contains
				// encrypted informations and if we store the
				// note in the cache, we'll store the
				// unencrypted data ==> DON'T DO THIS
				$bCache = false;
			}

			if ($bCache) {
				// Save the content in the cache
				$arr['from_cache'] = 1;
				$duration = $arrSettings['duration']['html'];
				$cached->set($arr)->expiresAfter($duration)->addTag(md5($filename));

				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}
		}

		$markdown = $arr['markdown'];

		return $markdown;
	}
}
