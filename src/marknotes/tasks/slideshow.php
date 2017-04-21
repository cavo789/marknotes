<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class SlideShow
{
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    } // function __construct()

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new SlideShow();
        }

        return self::$_Instance;
    } // function getInstance()

    /**
     * Convert the note written in markdown for remark framework
     *
     * Note: Remark use markdown and not HTML
     */
    private function remark(string $markdown, array $params) : string
    {
        return $markdown;
    }

    /**
     * Convert the note written in markdown for reveal framework
     *
     * Note: Remark use HTML
     */
    private function reveal(string $markdown, array $params) : string
    {
    }

    public function run(array $params)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if ($params['filename'] !== "") {
            $fullname = $aeSettings->getFolderDocs(true).$params['filename'];

            if (!$aeFiles->fileExists($fullname)) {
                echo str_replace('%s', '<strong>'.$fullname.'</strong>', $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists'));
                return;
            }

            // Read the markdown file
            $aeMarkDown = \MarkNotes\FileType\Markdown::getInstance();

            // Keep encrypted datas and show them unencrypted
            $params['removeConfidential'] = '0';

            $markdown = $aeMarkDown->read($fullname, $params);

            // Don't keep the § (tags prefix) for slideshow
            $markdown = str_replace($aeSettings->getTagPrefix(), '', $markdown);

            // Try to retrieve the heading 1
            $pageTitle = $aeMarkDown->getHeadingText($markdown, '#');

            // Check if the params array contains a "type" entry and if so, check if that type is valid i.e.
            // mention the name of an existing templates.  "remark" or "reveal" are supported in the version 1.0.7
            // of MarkNotes.

            $layout = '';

            if (isset($params['layout'])) {
                // $type will be initialized to an empty string if the file wasn't found in the /templates folder
                $layout = $aeFiles->sanitizeFileName($params['layout']);
                if ($aeSettings->getTemplateFile($layout) === '') {
                    $layout = '';
                }
            }

            if ($layout === '') {
                // Get the type from the settings.json file
                $layout = $aeSettings->getSlideshowType();
            }

            if (!isset($params['layout'])) {
                $params['layout'] = $layout;
            }

            //  Finally convert to slides by calling the correct framework
            if ($layout === 'remark') {
                $slides = $this->remark($markdown, $params);
            } else {
                $slides = $this->reveal($markdown, $params);
            }
        } // if ($filename!="")

        // Get the remark template
        $slideshow = file_get_contents($aeSettings->getTemplateFile($params['layout']));

        // $slideshow contains the template : it's an html file with (from the /templates folder)
        // and that file contains variables => convert them
        $aeHTML = \MarkNotes\FileType\HTML::getInstance();
        return $aeHTML->replaceVariables($slideshow, $slides, $params);
    }
}
