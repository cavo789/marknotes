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
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    } // function __construct()

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new Display();
        }

        return self::$_Instance;
    } // function getInstance()

    public function run(array $params)
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
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
            $aeFunctions->fileNotFound($fullname);
        }

        // Read the markdown file
        $markdown = $aeMD->read($fullname, $params);

        $fnameHTML = $aeFiles->replaceExtension($fullname, 'html');

        $fnameHTMLrel = str_replace(str_replace('/', DS, $aeSettings->getFolderWebRoot()), '', $fnameHTML);

        // Generate the URL (full) to the html file, f.i. http://localhost/docs/folder/file.html
        $urlHTML = rtrim($aeFunctions->getCurrentURL(false, true), '/').'/'.str_replace(DS, '/', $fnameHTMLrel);

        // Convert the Markdown text into an HTML text
        $aeConvert = \MarkNotes\Helpers\Convert::getInstance();
        $html = $aeConvert->getHTML($markdown, $params);

        // Check if the .html version of the markdown file already exists; if not, create it
        if (!$aeFunctions->isAjaxRequest()) {
            $aeConvert = \MarkNotes\Tasks\Converter\HTML::getInstance();
            return $aeConvert->run($html, $params);
        } else { // if (!\MarkNotes\Functions::isAjaxRequest())

            // Generate the URL (full) to the html file, f.i. http://localhost/docs/folder/file.html
            $fnameHTML = str_replace('\\', '/', rtrim($aeFunctions->getCurrentURL(false, true), '/').str_replace(str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME'])), '', $fnameHTML));

            include_once dirname(__DIR__)."/view/toolbar.php";
            $aeToolbar = \MarkNotes\View\Toolbar::getInstance();

            $html = $aeToolbar->getToolbar($params).'<div id="icon_separator" class="only_screen"/><div id="note_content">'.$html.'</div>';

            $html = '<div class="hidden filename">'.utf8_encode($fullname).'</div>'.$html.'<hr/>';

            // --------------------------------
            // Call content plugins
            $aeEvents->loadPlugins('content', 'html');
            $args = array(&$html);
            $aeEvents->trigger('render.content', $args);
            $html = $args[0];
            // --------------------------------
        } // if (!\MarkNotes\Functions::isAjaxRequest())
        return $html;
    }
}
