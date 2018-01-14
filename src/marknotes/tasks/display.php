<?php
/**
 * Return the HTML rendering of a .md file
 */
namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

// For third parties libraries
include 'libs/autoload.php';

class Display
{
	protected static $hInstance = null;

	public function __construct()
	{
		return true;
	} // function __construct()

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Display();
		}

		return self::$hInstance;
	} // function getInstance()

	private function insertHR(&$markdown)
	{
		// Convert any '---' (or '-----') to a new line (<hr/>) only if
		// preceded and followed by an empty line, so, like this :
		//
		//                  (empty line)
		//      ---
		//                  (empty line)
		//
		// Needed because there is sometimes bugs in MarkDown Extra when '---' follow
		// the declaration of an array

		$matches = array();
		preg_match_all('/\n\r?-{3,5}(\s*(\n\r?)*)*/', $markdown, $matches);
		foreach ($matches[0] as $tmp) {
			$markdown = str_replace($tmp, '<hr/>'.PHP_EOL, $markdown);
		}

		return true;
	}

	private function insertPageBreak(&$markdown)
	{

		// Convert any '***' to a page break only if, twice and if
		// preceded and followed by an empty line, so, like this :
		//
		//                  (empty line)
		//      ***
		//      ***
		//                  (empty line)
		//

		$matches = array();
		preg_match_all('/\n\r?(\*{3,5}(\s*(\n\r?)*)*){2}(\s*\n\r?)*/', $markdown, $matches);
		$break='<p style="page-break-after: always;">&nbsp;</p>'.
		   '<p style="page-break-before: always;">&nbsp;</p>';
		foreach ($matches[0] as $tmp) {
			$markdown = str_replace($tmp, $break.PHP_EOL, $markdown);
		}

		return true;
	}

	public function run(array $params)
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// If the filename doesn't mention the file's extension, add it.
		if (substr($params['filename'], -3) != '.md') {
			$params['filename'] = $aeFiles->removeExtension($params['filename']).'.md';
		}

		$fullname = $aeSettings->getFolderDocs(true).ltrim($params['filename'], DS);
		$fullname = str_replace('/', DS, $fullname);

		if (!$aeFiles->exists($fullname)) {
			// Damned ! It's so difficult to work with
			// accentuated chars and make the
			// code works both on Windows and Unix...
			$fullname = utf8_decode($fullname);
			if (!$aeFiles->exists($fullname)) {
				$aeFunctions->fileNotFound($fullname);
			}
		}

		// Read the markdown file, $markdown will contains
		// markdown content, not HTML one
		$aeMD = \MarkNotes\FileType\Markdown::getInstance();
		$markdown = $aeMD->read($fullname, $params);

		self::insertHR($markdown);
		self::insertPageBreak($markdown);

		// Convert the Markdown text (.md file's content)
		// into an HTML text
		$aeConvert = \MarkNotes\Helpers\Convert::getInstance();
		$htmlNote = $aeConvert->getHTML($markdown, $params, true);

		// Now, get the template and add the content
		// (from $htmlNote) in the page
		$html = '';
		if (trim($htmlNote)!=='') {
			$aeConvert = \MarkNotes\Tasks\Converter\HTML::getInstance();
			$html = $aeConvert->run($htmlNote, $params);
		}

		return $html;
	}
}
