<?php

namespace MarkNotes\Plugins\Markdown;

use \Symfony\Component\Yaml\Yaml;

defined('_MARKNOTES') or die('No direct access allowed');

class Read
{

	private static $sep='---';

	private static function replaceSpecialCharacters(string $markdown) : string
	{

		if (trim($markdown) == '') {
			return $markdown;
		}

		// Replace non breaking space to spaces since, otherwise, rendering will fail
		// and no output will be done for the sentence/paragraph with that character.
		// A non breaking space is U+00A0 (Unicode) but encoded as C2A0 in UTF-8
		// Replace by a space character

		try {
			$markdown=preg_replace('/\x{00a0}/siu', ' ', $markdown);
			if ($markdown == null) $markdown='';
		} catch (Exception $e) {

			/*<!-- build:debug -->*/
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log($e->getMessage(),"error",3);
			/*<!-- endbuild -->*/
		}

		return $markdown;
	}

    /**
     * Notes written in .md can contains variables.  The function below will
	 * translate these variables.
     */
    private static function replaceVariables(array $params) : string
    {

        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeLanguage = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		// Get the web root like http://localhost/notes/
        $sRoot = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/';

		// markdown content
		$markdown=$params['markdown'];

		// Processing filename
		// f.i. c:\sites\notes\docs\marknotes\french\userguide.md
		$filename=str_replace('/', DS,$params['filename']);

		// Get the relative folder; like docs/marknotes/french/
        $sFolder=str_replace($aeSettings->getFolderDocs(true), '', dirname($filename)).'/';
		$sFolder=str_replace(DS, '/', $aeSettings->getFolderDocs(false).$sFolder);

		$task = $aeSession->get('task');

		// Keep variables during the editing
		if ($task!=='edit.form') {

			// Remove HTML comments ?
			$removeComments = boolval($aeSettings->getPage('remove_html_comments', 0));
			if ($removeComments) $markdown=$aeFunctions::removeHTMLComments($markdown);

			$markdown = str_replace('%ROOT%', $sRoot, $markdown);

			if (strpos($markdown, '%LASTUPDATE%')!==FALSE) {

				// Retrieve the text to use for the rendering.
				// This text is stored in settings.json -> options -> lastupdate

				$arrSettings = $aeSettings->getPlugins('options', 'lastupdate');

				// Default will be '**Last update : %s**
				$text = $arrSettings['text'] ?? '**Last update : %s**';

				$date=utf8_encode(ucfirst(strftime($aeSettings->getText('date'), filemtime($filename))));

				$markdown = str_replace('%LASTUPDATE%', sprintf($text, $date), $markdown);

			}

			// The %URL% variable should be relative to this note ($sFile)
			// and not from the master note i.e. where the %INCLUDE% tag
			// has been put
			//
			// domPDF requires URL not filename

			if(!in_array($task,array('epub','docx'))) {
				// This is an hyperlink
				$markdown = str_replace('%URL%', str_replace(' ', '%20', $sRoot.$sFolder), $markdown);
			} else {
				// When exporting the note, should be a local file
				// Escape the directory separator (since under Windows,
				// it's a backslash \ which has special meaning in a regex)
				$markdown = str_replace('%URL%', str_replace(DS, '\\'.DS,rtrim(dirname($filename)).DS), $markdown);

			}

			if (strpos($markdown, '%NOTE_FOLDER%')!==FALSE) {

				$tmp=rtrim($aeSettings->getFolderWebRoot().$sFolder);
				$tmp=str_replace('/',DS,$tmp);

		        $markdown = str_replace('%NOTE_FOLDER%', rtrim($tmp, DS).DS, $markdown);

			} // if (strpos($markdown, '%NOTE_FOLDER%')!==FALSE)

			$tmp=$aeSettings->getFolderDocs(false);
			$tmp=str_replace('/',DS,$tmp);
	        $markdown = str_replace('%DOCS%', rtrim($tmp, DS), $markdown);
		}

        return $markdown;
    }

	/**
	 * When the note doesn't contains yet a YAML header, add one
	 */
	private static function BuildYAML(string &$md) : array
	{

        $aeSettings = \MarkNotes\Settings::getInstance();

		// Retrieve the title for the section, from settings.json
		$arrSettings = $aeSettings->getPlugins('options', 'yaml');

		// Retrieve the page title i.e. the first heading 1.
		$aeMarkDown = \MarkNotes\FileType\Markdown::getInstance();
		$pageTitle = $aeMarkDown->getHeadingText($md, '#');

		$arr=array();

		$arr['title']=trim($pageTitle);

		// Check if there are default values and if yes, add them
		$defaults=$arrSettings['defaults'];

		if (count($defaults)>0) {

			foreach ($defaults as $key=>$value) {

				switch ($value) {
					case '%TODAY%':
						$datetime = new \DateTime();
						// International format
						$value=$datetime->format('Y\-m\-d\ H:i:s');
				        break;
					case '%LANGUAGE%':
						$value=$aeSettings->getLanguage();
				        break;
				} // switch

				$arr[$key] = $value;

			} // foreach

		} // if (count($defaults)>0)

		return $arr;

	}

    /**
     * The markdown file has been read, this function will get the content of the .md file and
     * make some processing like data cleansing
     *
     * $params is a associative array with, as entries,
     *		* markdown : the markdown string (content of the file)
     *		* filename : the absolute filename on disk
     */
    public static function readMD(&$params = null)
    {

        if (trim($params['markdown']) === '') {
            return true;
        }

		// Extract the YAML header if any i.e. the block at the top of the file
		// starting with '---' on a line and ending with '---' on an another line
		//
		// For instance :
		//
		// ---
		// title: An amazing YAML block
		// author: Christophe Avonture
		// company: marknotes
		// date: tuesday 1st january 2018
		// ---

		$aeSession = \MarkNotes\Session::getInstance();
		$aeSession->set('yaml','');

        $aeSettings = \MarkNotes\Settings::getInstance();
		$lib=$aeSettings->getFolderLibs()."symfony/yaml/Yaml.php";

		if (is_file($lib)) {

			include_once $lib;

			$md = $params['markdown'];

			$quote = function ($str) {
	            return preg_quote($str, "~");
	        };

			// This code is coming from mnapoli/FrontYAML script
			// https://github.com/mnapoli/FrontYAML/blob/master/src/Parser.php#L63

			$regex = '~^('
	            .implode('|', array_map($quote, array(static::$sep))) # $matches[1] start separator
	            ."){1}[\r\n|\n]*(.*?)[\r\n|\n]+("              # $matches[2] between separators
	            .implode('|', array_map($quote, array(static::$sep))) # $matches[3] end separator
	            ."){1}[\r\n|\n]*(.*)$~s";                      # $matches[4] document content

	        if (preg_match($regex, $md, $matches) === 1) {

				// Get the YAML header like a string ($matches[2]) and convert it into an array
				$yaml = trim($matches[2]);

				// Retrieve the note's content, without the YAML header
	            $md = trim($matches[4]);

				$params['yaml'] = Yaml::parse($yaml);
				$params['markdown'] = $md;

				// Remember the note's YAML header
				$aeSession->set('yaml',$yaml);

	        } else {

				// There is no YAML block yet

				// Retrieve the title for the section, from settings.json
				$arrSettings = $aeSettings->getPlugins('options', 'yaml');

				// Check if we can add the block automatically
				$add=(bool)($arrSettings['add_if_missing']??0);

				if ($add) {

					$params['yaml'] = self::BuildYAML($md);

					// Remember the note's YAML header
					$aeSession->set('yaml',YAML::dump($params['yaml']));

				} // if ($add)

			}

		} // if (is_file($lib))

		$aeSession = \MarkNotes\Session::getInstance();
		$task = $aeSession->get('task');

		if ($task==='edit.form') {
			// In the edit form, the single "&" character won't be correctly interpreted
			// The regex here below will retrieve every & and not &amp;
			// If there are occurences, replace & by &amp;
			$matches = array();
	        if (preg_match_all('/&(?![A-Za-z]+;|#[0-9]+;)/m', $params['markdown'], $matches)) {
	            foreach ($matches as $match) {
					$params['markdown'] = str_replace($match, '$$$$', $params['markdown']);
					$params['markdown'] = str_replace('$$$$', '&amp;', $params['markdown']);
	            }
	        }

			if (isset($params['yaml'])) {
				$params['markdown']=static::$sep.PHP_EOL.
				   Yaml::dump($params['yaml']).static::$sep.PHP_EOL.PHP_EOL.$params['markdown'] ;
			}
		}

        // Be sure to have content with LF and not CRLF in order to be able to use
        // generic regex expression (match \n for new lines)
        $params['markdown'] = str_replace("\r\n", "\n", $params['markdown']);

        // -----------------------------------------------------------------------
        // URL Cleaner : Make a few cleaning like replacing space char in URL or in image source
        // Replace " " by "%20"

        $matches = array();
        if (preg_match_all('/<img *src *= *[\'|"]([^\'|"]*)/', $params['markdown'], $matches)) {
            foreach ($matches[1] as $match) {
                $sMatch = str_replace(' ', '%20', $match);
                $params['markdown'] = str_replace($match, $sMatch, $params['markdown']);
            }
        }

        // And do the same for links
        $matches = array();
        if (preg_match_all('/<a *href *= *[\'|"]([^\'|"]*)/', $params['markdown'], $matches)) {
            foreach ($matches[1] as $match) {
                $sMatch = str_replace(' ', '%20', $match);
                $params['markdown'] = str_replace($match, $sMatch, $params['markdown']);
            }
        }

		$params['markdown'] = str_replace('&params', '&amp;param', $params['markdown']);

		$params['markdown'] = self::replaceVariables($params);
		$params['markdown'] = self::replaceSpecialCharacters($params['markdown']);

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {

		$aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('markdown.read', __CLASS__.'::readMD');

        return true;

    }
}
