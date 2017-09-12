<?php

/**
 * Make the markdown code proper by replacing "invalid" characters by
 * the correct ones.
 *
 * 1. Replace one of these characters by ...

 *    Search      Replace by
 *    ------      ----------
 *    “           `
 *    ”           `
 *
 * 2. replace non breaking spaces introduced by Pandoc when the .md file
 *    is the result of a conversion from .docx to .md
 * 3. replace CRLF (Windows) by LF (Unix)
 * 4. replace empty lines (or lines with only spaces) by one single empty lines
 * 5. remove unneeded spaces between ### and the title (like in "###    My title")
 * x. replace links to image with absolute filename by the %URL% variable
 * x. replace links to the folder where the note resides by the %NOTE_FOLDER% variable
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Clean_Code
{

    public static function readMD(&$params = null)
    {

        if (trim($params['markdown']) === '') {
            return true;
        }

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
		}
		/*<!-- endbuild -->*/

		$tmp=$params['markdown'];

		// ---------------------
		// 1. Replace characters
		$tmp=str_replace('“', '`', $tmp);
		$tmp=str_replace('”', '`', $tmp);

		/*<!-- build:debug -->*/
		if ($aeSettings->getDevMode()) {
			if ($tmp!==$params['markdown']) {
			   $aeDebug->log('   1. Replace characters has change something', 'debug');
		   }
		}

		// 2. Replace non breaking spaces introduced by Pandoc when the .md file
		// is the result of a conversion from .docx to .md
		// A non breaking space is U+00A0 (Unicode) but encoded
		// as C2A0 in UTF-8
		// Replace by a space character
		$tmp=preg_replace('/\x{00a0}/siu', ' ', $tmp);

		/*<!-- build:debug -->*/
		if ($aeSettings->getDevMode()) {
			if ($tmp!==$params['markdown']) {
			   $aeDebug->log('   2. Replace non breaking spaces has change something', 'debug');
		   }
		}
		/*<!-- endbuild -->*/

		// Replacements 1 & 2 are "mandatory" since can gives errors while interpreting
		// the file. So if something was changed by 1 or 2, rewrite the file on disk

		if ($tmp!==$params['markdown']) {

			// 3. Optional. Be sure to have a file with LF (linefeed) and
			// not CRLF (carriage return followed by linefeed i.e. windows OS file)
			$previous=$tmp;
			$tmp=preg_replace('/\r\n?/', "\n", $tmp);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDevMode()) {
				if ($tmp!==$previous) {
				   $aeDebug->log('   3. Replace CRLF to LF', 'debug');
			   }
			}
			/*<!-- endbuild -->*/

			// 4. With markdown language there is no need to have more than three linefeed
			// (no empty lines)
			$previous=$tmp;
			$tmp=preg_replace('/\n{3,}/si', "\n\n", $tmp);
			// and no need to have a line with space characters
			$tmp=preg_replace('/\n {1,}\n/si', "\n\n", $tmp);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDevMode()) {
				if ($tmp!==$previous) {
				   $aeDebug->log('   4. Replace too many empty lines', 'debug');
			   }
			}
			/*<!-- endbuild -->*/

			// 5. No need to have more than one space between a title (#) and the text
			// So replace "###    My title" by "### My title"
			// $1 will match the # char(s) (so "###")
			// $2 will match spaces  (so "    " in the example)
			// $3 will match the title (so "My title")
			//
			// So, the result is :
			// "$1 $3"   ==> give the headings followed by only one space followed by title
			$previous=$tmp;
			$tmp=preg_replace('/^(#{1,})( ){2,}(.*)/m', "$1 $3", $tmp);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDevMode()) {
				if ($tmp!==$previous) {
				   $aeDebug->log('   5. Remove unneeded space after # sign', 'debug');
			   }
			}
			/*<!-- endbuild -->*/

			// ---------------------
			// Replace file location to the %URL% variable
			$sURL=$aeFunctions->getCurrentURL();
			$sURL.=str_replace(DS, '/', dirname($aeSettings->getFolderDocs(false).$aeSession->get('filename'))).'/';
			$sURL=str_replace(' ', '%20', $sURL);
			$tmp=str_replace($sURL, '%URL%', $tmp);

			// Replace links to the folder where the note resides by the
			// %NOTE_FOLDER% variable
			$folder=rtrim(str_replace('/', DS, dirname($params['filename'])), DS);
			$tmp = str_replace($folder.DS, '%NOTE_FOLDER%', $tmp);
			$tmp = str_replace($folder, '%NOTE_FOLDER%', $tmp);

			// -----------------------------------------------------
			// In case of a master file with a %INCLUDE chapter1.md%
			// Backup the filename that is being displayed (f.i. master.md)
			$originalFile=$aeSession->get('filename','');

			// While $params['filename'] is perhaps an included file (like chapter1.md).
			// The markdown.write event should be done on that file, not the
			// 'master' one.
			$aeSession->set('filename',$params['filename']);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('Rewrite '.$params['filename'], 'debug');
			}
			/*<!-- endbuild -->*/

			// Rewrite the file on the disk so remove img tags
			$aeEvents = \MarkNotes\Events::getInstance();
	        $aeEvents->loadPlugins('markdown');
	        $args = array(&$tmp);
	        $aeEvents->trigger('markdown.write', $args);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('Rewrite DONE '.$params['filename'], 'debug');
			}
			/*<!-- endbuild -->*/

			// In the form, keep the %URL% variable and not the full path
			// to the image
			if ($aeSession->get('task', '')=='edit.form') {
				$params['markdown'] = $args[0];
			}

			// Restore the filename in the session object
			$aeSession->set('filename',$originalFile);

		} // if ($tmp!==$markdown)

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

		if(!in_array($task, array('search'))) {
			$aeEvents->bind('markdown.read', __CLASS__.'::readMD');
		}

		return true;
	}
}
