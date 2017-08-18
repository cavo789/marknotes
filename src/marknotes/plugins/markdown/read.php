<?php

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Read
{
    /**
     * Notes written in .md can contains variables.  The function below will translate these variables.
     */
    private static function replaceVariables(string $markdown) : string
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // Get the web root like http://localhost/notes/
        $sRoot = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/';

        // Get the relative folder; like docs/folder/
        $sFolder = str_replace(DS, '/', dirname($aeSettings->getFolderDocs(false).$aeSession->get('filename'))).'/';

		$task = $aeSession->get('task');

		// Keep variables during the editing
		if ($task!=='edit.form') {
	        $markdown = str_replace('%ROOT%', $sRoot, $markdown);
			$markdown = str_replace('%URL%', str_replace(' ', '%20', $sRoot.$sFolder), $markdown);
	        $markdown = str_replace('%DOCS%', rtrim($aeSettings->getFolderDocs(false), DS), $markdown);
		}

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
    public static function readMD(&$params = null)
    {
        if (trim($params['markdown']) === '') {
            return true;
        }

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

        $params['markdown'] = self::replaceVariables($params['markdown']);

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
