<?php

/**
 * Working on big files isn't always the most efficient way.
 * This plugin will allow to include files in the markdown content "just like"
 * if the content was in a single file.
 *
 * For instance :
 *
 *   # My big story
 *
 *   %INCLUDE .chapters/chapter1.md%
 *   %INCLUDE .chapters/chapter2.md%
 *   %INCLUDE .chapters/chapter3.md%
 *
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Include_File
{

    private static function getURL(string $filename) : string {

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the web root like http://localhost/notes/
        $sRoot = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/';

		// Add the /docs folder in the URL (so something like http://localhost:8080/notes/docs/)
		$url = $sRoot.rtrim($aeSettings->getFolderDocs(false), DS).'/';

		// Get the relative folder of the notes (from /docs)
		// Something like (/marknotes/userguide)
		$path=str_replace($aeSettings->getFolderDocs(true), '', dirname($filename));

		$url.=rtrim(str_replace(DS, '/', $path),'/').'/';

		// And return the url http://localhost:8080/notes/docs/marknotes/userguide)
		return str_replace(' ', '%20', $url);
    }

    private static function processIncludes(string $markdown, string $filename, string $indent = '') : string {

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

    	// The %INCLUDE ...% tag should start the line. If there is one or more character before
    	// (for instance a single space) consider the tag not active.
    	// So " %INCLUDE ..." won't work and can be therefore use to temporary disable the tag f.i.
        if (preg_match_all('/^([ \\t])*%INCLUDE (.*)%/m', $markdown, $matches)) {

            $aeSession = \MarkNotes\Session::getInstance();
    		$task = $aeSession->get('task');

    		$aeFiles = \MarkNotes\Files::getInstance();
    		$aeFunctions = \MarkNotes\Functions::getInstance();
    		$aeSession = \MarkNotes\Session::getInstance();

    		// Retrieve the note fullpath
    		$root = rtrim($aeSettings->getFolderDocs(true), DS).DS;
    		$root = $root.dirname($aeSession->get('filename')).DS;

    		// Loop and process every %INCLUDE ..% tags

    		$i=0;

    		for($i=0; $i<count($matches[0]); $i++) {

    			// $tag    => $matches[0][0] will be f.i. "  %INCLUDE .chapters/chapter1.md%"
    			// $before => $matches[1][0] will be f.i. "  "          // What's before %INCLUDE
    			// $file   => $matches[2][0] will be f.i. ".chapters/chapter1.md"
    			list($tag, $before, $file) = $matches;

    			// Get the filename to include
    			$sFile = realpath(str_replace('/',DS,$file[$i]));

    			/*<!-- build:debug -->*/
                if ($aeSettings->getDebugMode()) {
                    $aeDebug->log($indent.'Including '.$sFile, 'debug');
                }
                /*<!-- endbuild -->*/

    			if ($before[$i]=='') {

    				// Ok, no space before the tag => it's enabled => run it

    				if (is_file($sFile)) {

    					// And if found, get its content and replace the tag
    					$content = trim(file_get_contents($sFile));

						// Remove HTML comments ?
 						$removeComments = boolval($aeSettings->getPage('remove_html_comments', 0));
						if ($removeComments) $content=$aeFunctions::removeHTMLComments($content);

						if (strpos($content, '%URL%') !== false) {

							// The %URL% variable should be relative to this note ($sFile)
							// and not from the master note i.e. where the %INCLUDE% tag has
							// been put
							$content = str_replace('%URL%', self::getURL($sFile), $content);

						}

						// Now, perhaps that note (the included one) also contains
    					// %INCLUDE ...% tag so ... process it again

						while (strpos($content, '%INCLUDE ') !== false) {

							$content = self::processIncludes($content, $sFile, $indent.'   ');

						}

    					$markdown = str_replace($tag[$i], $content, $markdown);

    				} else { // if (is_file($sFile))

    					/*<!-- build:debug -->*/
    		            if ($aeSettings->getDebugMode()) {
    		                $aeDebug->log('   Failure : file not found ! If the path is relative, think to add %NOTE_FOLDER% in your call so the file will be correctly retrieved (f.i. %INCLUDE %NOTE_FOLDER%file-to-include.md%)', 'error');
    		            }
    		            /*<!-- endbuild -->*/

    				}// if (is_file($sFile))

    			} else { // if ($before[$i]=='')

    				/*<!-- build:debug -->*/
    				if ($aeSettings->getDebugMode()) {
    					$aeDebug->log($indent.' - Not processed since the tag isn\'t at the begining of the sentence. If there are characters before the %INCLUDE% tag, the tag is ignored', 'debug');
    					$aeDebug->log($indent.' ***'.$tag[$i].'***','debug');
    				}
    				/*<!-- endbuild -->*/

    				// there is at least one space before the tag => it's not active

    				if (!in_array($task, array('display','main','md'))) {

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

    public static function readMD(&$params = null)
    {

        if (trim($params['markdown']) === '') {
            return true;
        }

        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

		$task = $aeSession->get('task');
		$filename = $aeSettings->getFolderDocs(true).$aeSession->get('filename');

		// Don't change anything if the note is displayed through the edit form
		if($task!=='edit.form') {

            $params['markdown'] = self::processIncludes($params['markdown'], $filename, '');

		} // if($task!=='edit.form')

        return true;

    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

		$task=$aeSession->get('task', '');

        // Fire this plugin only for the specified task
        //if (!in_array($aeSession->get('task'), array('main','display'))) {
        //    return false;
        //}

        $aeEvents->bind('markdown.read', __CLASS__.'::readMD');
        return true;
    }
}
