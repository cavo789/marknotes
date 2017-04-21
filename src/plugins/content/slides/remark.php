<?php

namespace MarkNotes\Plugins\Content\Slides;

defined('_MARKNOTES') or die('No direct access allowed');

class Remark
{
    /**
     *
     */
    public static function doIt(&$params = null)
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

            // The slideshow functionnality will be remark

            // Consider that every headings (except h1) should start in a new slide
            // The "remark" library allow indeed to give a name to each slide by just adding "name: NAME" in the markdown string

            $arrHeading = array('##','###','####','#####','######');
            foreach ($arrHeading as $head) {
                $matches = array();

                preg_match_all("/\\n".$head." (.*)/", $markdown, $matches);

                if (count($matches) > 0) {
                    // Process one by one
                    $j = count($matches[0]);

                    for ($i = 0; $i < $j; $i++) {
                        // $matches[0][$i] is f.i. "## TITLE" while $matches[1][$i] is "TITLE"
                        //
                        // remark allow to specify the name of the slide so add a "name:" property in the markdown like this :
                        //
                        //   name: TITLE
                        //   ---
                        //   ## TITLE

                        $markdown = str_replace(
                            $matches[0][$i],
                            //"???".PHP_EOL.str_replace('/',DS,$filename).PHP_EOL.  // Add speaker note : ??? followed by a line and the text
                            "---".PHP_EOL.
                            "name: ".rtrim($matches[1][$i], " #").PHP_EOL.        // Be sure to not have a title like ## Heading2 ## (==> remove final # and space if there are ones)
                            ".footnote[.italic[".$pageTitle."]]".PHP_EOL.
                            $matches[0][$i],
                            $markdown
                        );
                    } // for ($i)
                } // if(count($matches)>0)
            } // foreach ($arrHeading as $head)

            // -------------------
            // Consider an <hr> (can be <hr   >, <hr  />, ...) as a new slide

            $matches = array();
            preg_match_all('/-{3,5}/', $markdown, $matches);
            foreach ($matches[0] as $tmp) {
                $markdown = str_replace($tmp, '---', $markdown);
            }

            // -----------------------
            // Get the remark template
            $slideshow = file_get_contents($aeSettings->getTemplateFile('remark'));

            // $slideshow contains the template : it's an html file with (from the /templates folder)
            // and that file contains variables => convert them
            $aeHTML = \MarkNotes\FileType\HTML::getInstance();
            $html = $aeHTML->replaceVariables($slideshow, $markdown, $params);
        } // if ($params['filename'] !== "")

        // The slideshow is now created, no need to process other slideshow plugins
        $params['stop_processing'] = true;

        // And return the HTML to the caller
        $params['html'] = $html;

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.slides', __CLASS__.'::doIt');
        return true;
    }
}
