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
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    } // function __construct()

    public static function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new Display();
        }

        return self::$_instance;
    } // function getInstance()

    /**
     * Display the HTML rendering of the note in a nice HTML layout. Called when the URL is something like
     * http://localhost/notes/docs/Development/atom/Plugins.html i.e. accessing the .html file
     *
     * @param  string  $html [description]   html rendering of the .md file
     * @return {[type]       Nothing
     */
    private function showHTML(string $html, array $params = null)
    {

        $aeFiles=\MarkNotes\Files::getInstance();
        $aeHTML=\MarkNotes\FileType\HTML::getInstance();
        $aeSettings=\MarkNotes\Settings::getInstance();

        // Add h2 and h3 id and don't add the "go to top" icon
        $html=$aeHTML->addHeadingsID($html, false);

        // Add css to bullets
        $html=$aeHTML->setBulletsStyle($html);

        // Check if a template has been specified in the parameters
        // and if so, check that this file exists

        // Default is html
        $template=$aeSettings->getTemplateFile('html');

        if (isset($params['template'])) {
            $template=$aeSettings->getTemplateFile($params['template']);
            if (!$aeFiles->fileExists($template)) {
                $template=$aeSettings->getTemplateFile('html');
            }
        }

        // Don't keep the § (tags prefix) for slideshow
        $html=str_replace($aeSettings->getTagPrefix(), '', $html);
        $html=str_replace(htmlentities($aeSettings->getTagPrefix()), '', $html);  // &sect; = §

        if ($aeFiles->fileExists($template)) {
            $html=$aeHTML->replaceVariables(file_get_contents($template), $html, $params);
        }

        $javascript=
        "\nvar markdown = {};\n".
        "markdown.message={};\n".
        "markdown.message.on_this_page='".$aeSettings->getText('on_this_page', 'On this page', true)."';\n";

        $html=str_replace('<!--%ADDITIONNAL_JS%-->', '<script type="text/javascript">'.$javascript.'</script>', $html);

        return $html;
    }

    public function run(array $params)
    {
        $aeFiles=\MarkNotes\Files::getInstance();
        $aeFunctions=\MarkNotes\Functions::getInstance();
        $aeSettings=\MarkNotes\Settings::getInstance();
        $aeMD=\MarkNotes\FileType\Markdown::getInstance();

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3)!='.md') {
            $params['filename'].='.md';
        }

        $fullname=str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $aeSettings->getFolderDocs(true).
                ltrim($params['filename'], DS)
        );

        if (!$aeFiles->fileExists($fullname)) {
            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                echo __FILE__.' - '.__LINE__.'<br/>';
            }
            /*<!-- endbuild -->*/

            echo str_replace(
                '%s',
                '<strong>'.$fullname.'</strong>',
                $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists')
            );
            return false;
        }

        // Read the markdown file
        $markdown=$aeMD->read($fullname, $params);

        $fnameHTML=$aeFiles->replaceExtension($fullname, 'html');

        $fnameHTMLrel=str_replace(str_replace('/', DS, $aeSettings->getFolderWebRoot()), '', $fnameHTML);

        // Generate the URL (full) to the html file, f.i. http://localhost/docs/folder/file.html
        $urlHTML = rtrim($aeFunctions->getCurrentURL(false, true), '/').'/'.str_replace(DS, '/', $fnameHTMLrel);

        // Convert the Markdown text into an HTML text
        $aeConvert=\MarkNotes\Helpers\Convert::getInstance();
        $html=$aeConvert->getHTML($markdown, $params);

        // Check if the .html version of the markdown file already exists; if not, create it
        if (!$aeFunctions->isAjaxRequest()) {
            return self::showHTML($html, $params);
        } else { // if (!\MarkNotes\Functions::isAjaxRequest())
            // -----------------------------------------------------------------------
            // Check if the file contains words present in the tags.json file : if the file being displayed
            // contains a word (f.i. "javascript") that is in the tags.json (so it's a known tag) and that
            // word is not prefixed by the "§" sign add it : transform the "plain text" word and add the "tag" prefix

            if ($aeFiles->fileExists($fname = $aeSettings->getFolderWebRoot().'tags.json')) {
                if (filesize($fname)>0) {
                    $aeJSON=\MarkNotes\JSON::getInstance();

                    $arrTags=$aeJSON->json_decode($fname);

                    foreach ($arrTags as $tag) {
                        // For each tag, try to find the word in the markdown file

                        // /( |\\n|\\r|\\t)+               Before the tag, allowed : space, carriage return, linefeed or tab
                        // [^`\/\\#_\-§]?                  Before the tag, not allowed : `, /, \, #, -, _ and § (the PREFIX_TAG)
                        // ('.preg_quote($tag).')          The tag term (f.i. "javascript"
                        // (\\n|,|;|\\.|\\)|[[:blank:]]|$) After the tag, allowed : carriage return, comma, dot comma, dot, ending ), tag or space or end of line

                        // Capture the full line (.* ---Full Regex--- .*)
                        preg_match_all('/(.*( |\\n|\\r|\\t|\\*|\\#)+('.preg_quote($tag).')(\\n|,|;|\\.|\\)|\\t|\\*|\\#| |$)*)/i', $markdown, $matches);

                        foreach ($matches[0] as $match) {
                            if (count($match)>0) {
                                preg_match('/(.*( |\\n|\\r|\\t|\\*|\\#)+('.preg_quote($tag).')(\\n|,|;|\\.|\\)|\\t|\\*|\\#| |$).*)/i', $match, $matches);

                                // Replace, in the line, the word f.i.    (don't use a preg_replace because preg_replace will replace all occurences of the word)

                                //   Line  : Start a SSH connexion     (original)
                                //   By    : Start a §SSH connexion    (new line)

                                // $matches[2] : what was just before the tag      f.i.   " Start a SSH, then ..."  => the space before SSH
                                // $matches[3] : the tag                                  " Start a SSH, then ..."  => SSH
                                // $matches[4] : what was just after the tag              " Start a SSH, then ..."  => the comma after SSH

                                $sLine=str_ireplace($matches[2].$matches[3].$matches[4], $matches[2].$aeSettings->getTagPrefix().$matches[3].$matches[4], $matches[0]);

                                // And now, replace the original line ($matches[0]) by the new one in the document.

                                $markdown=str_replace($matches[0], $sLine, $markdown);
                            } // if (count($match)>0)
                        } // foreach ($matches[0] as $match)
                    } // foreach
                } // if(filesize($fname)>0)
            } // if ($aeFiles->fileExists($fname=$this->_rootFolder.'tags.json'))

            //
            // -----------------------------------------------------------------------

            // Generate the URL (full) to the html file, f.i. http://localhost/docs/folder/file.html
            $fnameHTML = str_replace('\\', '/', rtrim($aeFunctions->getCurrentURL(false, true), '/').str_replace(str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME'])), '', $fnameHTML));

            include_once dirname(__DIR__)."/view/toolbar.php";
            $aeToolbar=\MarkNotes\View\Toolbar::getInstance();

            $html=$aeToolbar->getToolbar($params).'<div id="icon_separator" class="only_screen"/><div id="note_content">'.$html.'</div>';

            $html='<div class="hidden filename">'.utf8_encode($fullname).'</div>'.$html.'<hr/>';
        } // if (!\MarkNotes\Functions::isAjaxRequest())
        return $html;
    } // function Run()
} // class Display
