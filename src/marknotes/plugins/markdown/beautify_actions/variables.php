<?php
/**
 * Replace file location to the %URL% variable : if the user
 * has type, in his note, the /docs/folder/.images/an_image.jpg
 * in his note /docs/folder/image.md, then "/docs/folder/" can
 * be removed and replaced by %URL% since the .images folder is
 * relative to the note's folder
 */
namespace MarkNotes\Plugins\Markdown\Beautify_Actions;

defined('_MARKNOTES') or die('No direct access allowed');

class Variables
{
	public function doIt(array $params) : string
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$markdown = $params['markdown'];

		$sURL=$aeFunctions->getCurrentURL();
		$sURL.=str_replace(DS, '/', dirname($aeSettings->getFolderDocs(false).$aeSession->get('filename'))).'/';
		$sURL=str_replace(' ', '%20', $sURL);
		$markdown=str_replace($sURL, '%URL%', $markdown);

		// Replace links to the folder where the note resides by the
		// %NOTE_FOLDER% variable
		$folder=rtrim(str_replace('/', DS, dirname($params['filename'])), DS);
		$markdown = str_replace($folder.DS, '%NOTE_FOLDER%', $markdown);
		$markdown = str_replace($folder, '%NOTE_FOLDER%', $markdown);

		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			if ($params['markdown']!==$markdown) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("   ".__CLASS__." has modify the markdown content", "debug");
			}
		}
		/*<!-- endbuild -->*/

		return $markdown;
	}
}
