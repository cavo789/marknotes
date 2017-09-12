<?php

/**
 * Add a numbering in front off each heading (h1 -> h6)
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class HeadNumbering
{

    /**
     * The markdown file has been read, this function will get the content of the .md file
     *
     * $params is a associative array with, as entries,
     *	* markdown : the markdown string (content of the file)
     *	* filename : the absolute filename on disk
    */
    public static function readMD(&$params = null)
    {
        if (trim($params['markdown']) === '') {
            return true;
        }

		$aeFunctions = \MarkNotes\Functions::getInstance();

		// Retrieve any headings from the markdown content
		//
		// Capture headings like : "### 1.3. Annex"
		//
		// $tags = will contains the full match i.e. "### 1.3. Annex"
		// $heading = the "###" construction so the heading's level (H3 here)
		// $numbering = the current numbering ("1.3.") or nothing
		// $title = the title ("Annex")

        if (preg_match_all('/^(#{1,})\\s*([0-9.]*)?(.*)/m', $params['markdown'], $matches)) {

			list($tags, $headings, $numbering, $title) = $matches;

			// Get the deepest level (f.i. 6 if we've found ######)

			$deepestLevel=1;

			foreach($headings as $value) {
				if (strlen($value)>$deepestLevel) {
					$deepestLevel=strlen($value);
				}
			}

			// Initialize counters to 0 for each level

			$arrHeadings=array();
			for ($i=0; $i<$deepestLevel; $i++) {
				$arrHeadings[($i+1)] = 0;
			}

			// Process every headings
			for ($i=0; $i<count($headings); $i++) {

				$len=strlen($headings[$i]);

				if ($aeFunctions::startsWith($title[$i], DEV_MODE_PREFIX)) {
					continue;
				}

				for ($j=$len+1; $j<=$deepestLevel; $j++) {
					$arrHeadings[$j]=0;
				}

				$arrHeadings[$len]+=1;

				$sNumber = '';

				// Don't start the numbering to heading 1 (since there should be only
				// once by article (should be)). Start the number at $j=1 i.e. heading 2.
				for ($j=1; $j<$len; $j++) {
					$sNumber .= $arrHeadings[$j+1].'.';
				}

				$sNumber = ltrim($sNumber, '0.');

				$sTitle = $headings[$i].' '.($sNumber<>''?$sNumber.' ':'').trim($title[$i]);

				$params['markdown']=str_replace($tags[$i],$sTitle, $params['markdown']);

			} // for ($i

		} // if (preg_match_all

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
