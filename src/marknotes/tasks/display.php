<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

// For third parties libraries
include 'libs/autoload.php';

/**
* Return the HTML rendering of a .md file
*/
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
        $aeMD = \MarkNotes\FileType\Markdown::getInstance();

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3) != '.md') {
            $params['filename'] .= '.md';
        }

        $fullname = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $aeSettings->getFolderDocs(true).
                ltrim($params['filename'], DS)
        );

        if (!$aeFiles->fileExists($fullname)) {
            // Damned ! It's so difficult to work with accentuated chars and make the
            // code works both on Windows and Unix...
            $fullname = utf8_encode($fullname);
            if (!$aeFiles->fileExists($fullname)) {
                $aeFunctions->fileNotFound($fullname);
            }
        }

        // Read the markdown file
        $markdown = $aeMD->read($fullname, $params);

		self::insertHR($markdown);
		self::insertPageBreak($markdown);

        $fnameHTML = $aeFiles->replaceExtension($fullname, 'html');

        $fnameHTMLrel = str_replace(str_replace('/', DS, $aeSettings->getFolderWebRoot()), '', $fnameHTML);

        // Generate the URL (full) to the html file, f.i. http://localhost/docs/folder/file.html
        $urlHTML = rtrim($aeFunctions->getCurrentURL(false, true), '/').'/'.str_replace(DS, '/', $fnameHTMLrel);

        // Convert the Markdown text into an HTML text
        $aeConvert = \MarkNotes\Helpers\Convert::getInstance();
        $html = $aeConvert->getHTML($markdown, $params);

        if (!$aeFunctions->isAjaxRequest()) {
            $aeConvert = \MarkNotes\Tasks\Converter\HTML::getInstance();
            $html = $aeConvert->run($html, $params);
        } else { // if (!\MarkNotes\Functions::isAjaxRequest())

            // Generate the URL (full) to the html file, f.i. http://localhost/docs/folder/file.html
            $fnameHTML = str_replace('\\', '/', rtrim($aeFunctions->getCurrentURL(false, true), '/').str_replace(str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME'])), '', $fnameHTML));

            include_once dirname(__DIR__)."/view/toolbar.php";
            $aeToolbar = \MarkNotes\View\Toolbar::getInstance();

			//$html = $aeToolbar->getToolbar($params).'<div id="icon_separator" class="only_screen"/><div id="note_content">'.$html.'</div>';
			$html = $aeToolbar->getToolbar($params).'<div id="note_content">'.$html.'</div>';

            $html = '<div class="hidden filename">'.utf8_encode($fullname).'</div>'.$html.'<hr/>';

            // --------------------------------
            // Call content plugins
            $aeEvents->loadPlugins('content', 'html');
            $args = array(&$html);
            $aeEvents->trigger('render.content', $args);
            $html = $args[0];
            // --------------------------------
        } // if (!\MarkNotes\Functions::isAjaxRequest())

        // ----------------------------------------------
        //
        // Add JS

        $urlHTML = '';

        $filename = $aeSession->get('filename', '');
        if ($filename !== '') {
            $url = rtrim($aeFunctions->getCurrentURL(false, false), '/');
            $urlHTML = $url.'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
            $urlHTML .= str_replace(DS, '/', $aeFiles->replaceExtension($filename, 'html'));
        }

        $js = "";

        if ($aeSettings->getDebugMode()) {
            $js .= "\n<!-- Lines below are added by ".__FILE__."-->";
        }

        $js .= "<script type=\"text/javascript\">\n".
            "marknotes.note = {};\n".
            "marknotes.note.url = '".$urlHTML."';\n".
            "</script>\n";

        if ($aeSettings->getDebugMode()) {
            $js .= "<!-- End for ".__FILE__."-->";
        }

        return $html.$js;
    }
}
