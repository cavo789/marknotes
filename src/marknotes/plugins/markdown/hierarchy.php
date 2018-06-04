<?php
/**
 * Add a numbering in front off each heading (h1 -> h6)
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Hierarchy extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.hierarchy';
	protected static $json_options = '';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$dont = $params['dont_run_hierarchy']??0;
		if ($dont) {
			return true;
		}

		$aeFunctions = \MarkNotes\Functions::getInstance();

		// Retrieve any headings from the markdown content
		//
		// Capture headings like : "### 1.3. Annex"
		//
		// $tags = will contains the full match i.e. "### 1.3. Annex"
		// $heading = the "###" construction so the heading's
		// level (H3 here)
		// $numbering = the current numbering ("1.3.") or nothing
		// $title = the title ("Annex")

		// ----------------------------------------------
		// EXCEPTION
		// When including a source file like the one below
		// this hierarchy plugin SHOULD NOT process lines
		// started with a "#" in code bloc (i.e. between
		// "```vbnet" and "```")
		//
		// 		## title			==> Will becode 2.1 title
		// 		Here is the source code
		//
		// 		```vbnet
		// 		#Region "Imports"	==> SHOULD BE IGNORED
		// 		Imports System
		// 		#End Region
		// 		```
		//
		// 		## title			==> Will becode 2.2 title
		// 		bla bla
		// ----------------------------------------------

		$arrCodeBlock = array();
		$i=0;

		// EXCEPTION - Remove temporarily the code block
		// If we've code blocks, replace the current content by a
		// tag so
		// 		```vbnet
		// 		#Region "Imports"	==> SHOULD BE IGNORED
		// 		Imports System
		// 		#End Region
		//
		// 	will be replaced by
		//
		// 		@~@CODE_0@~@
		//
		// 	doing this, lines starting with "#" in the code won't be
		// 	processed as titles in the markdown text

		if (preg_match_all('~```[a-z]*\n[\s\S]*?\n```~m',$params['markdown'], $match_code)) {
			foreach($match_code as $code) {
				$arrCodeBlock[$i] = $code[0];
				$params['markdown'] = str_replace($code[0], '@~@CODE_'.$i.'@~@', $params['markdown']);
				$i++;
			}
		}

		if (preg_match_all('/^(#{1,})\\s*([0-9.]*)?(.*)/m', $params['markdown'], $matches)) {

			list($tags, $headings, $numbering, $title) = $matches;

			// Get the deepest level (f.i. 6 if we've found ######)
			$deepestLevel=1;
			foreach ($headings as $value) {
				if (strlen($value)>$deepestLevel) {
					$deepestLevel=strlen($value);
				}
			}

			// Initialize counters to 0 for each level
			$arrHeadings=array();
			for ($i=0; $i<$deepestLevel; $i++) {
				$arrHeadings[($i+1)] = 0;
			}

			$markdown=$params['markdown'];

			// Process every headings
			for ($i=0; $i<count($headings); $i++) {
				$len=strlen($headings[$i]);
				/*if ($aeFunctions::startsWith($title[$i], DEV_MODE_PREFIX)) {
					continue;
				}*/

				for ($j=$len+1; $j<=$deepestLevel; $j++) {
					$arrHeadings[$j]=0;
				}

				$arrHeadings[$len]+=1;
				$sNumber = '';

				// Don't start the numbering to heading 1 (since
				// there should be only once by article (should be)).
				// Start the number at $j=1 i.e. heading 2.
				for ($j=1; $j<$len; $j++) {
					$sNumber .= $arrHeadings[$j+1].'.';
				}

				$sNumber = ltrim($sNumber, '0.');

				$sTitle = $headings[$i].' '.($sNumber<>''?$sNumber.' ':'').trim($title[$i]);

				// Use preg_replace ( xx, xx, xx, 1) for replacing
				// only the first occurence.
				// Will act exactly like a str_replace but only
				// replace the first occurence which is really
				// important here when using numbering
				$markdown=preg_replace('~'.$tags[$i].'~', $sTitle, $markdown, 1);
			} // for ($i

			// EXCEPTION - Restore the code block
			// Here above, we've replaced code blocks to @~@CODE_0@~@
			// Do the opposite, restore the content once the numbering
			// has been added
			if (!empty($arrCodeBlock)) {
				foreach($arrCodeBlock as $key => $code) {
					$markdown = str_replace('@~@CODE_'.$key.'@~@', $code, $markdown);
				}
			} // if (!empty($arrCodeBlock))

			// Now, check if there is an added value to add number i.e.
			// if there is only one title, it isn't really not usefull to put
			// "1. MyTitle" since there is no "2. Something".

			$bDoIt=false;

			for ($i=0; $i<$deepestLevel; $i++) {
				if (!$bDoIt) {
					$bDoIt = ($arrHeadings[($i+1)]>1);
					// Only if we've at least two titles
					if ($bDoIt) {
						break;
					}
				}
			} // for

			if ($bDoIt) {
				$params['markdown']=$markdown;
			}
		} // if (preg_match_all
		return true;
	}
}
