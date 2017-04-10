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

        return $markdown;
    }

    /**
     * Convert the note written in markdown for reveal framework
     *
     * Note: Remark use HTML
     */
    private function reveal(string $markdown, array $params) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // A manual section break (i.e. a new slide) can be manually created in MarkNotes by just creating, in the
        // note a new line with --- (or -----).  Only these characters on the beginning of the line.
        //
        // WARNING : the string should be with LF (linefeed) only and not CRLF

        $newSlide = '\n+^-{3,5}$\n+';
        $imgTag = '\!\[.*\]\((.*)\)$\n';

        // Replace a slide with only an image (like below illustrated) by a section with a background image
        //    ---
        //    ![](.images/image.jpg)
        //    ---
        //$markdown=preg_replace('/'.$newSlide.'^\!\[\]\((.*)\)$\n^-{3,5}$\n/m', '******', $markdown);

        $matches = array();
        if (preg_match_all('/'.$newSlide.$imgTag.'/m', $markdown, $matches)) {
            $j = count($matches[0]);
            for ($i = 0; $i <= $j; $i++) {
                if (isset($matches[0][$i])) {
                    $markdown = str_replace($matches[0][$i], PHP_EOL.PHP_EOL.'###### @@@@'.base64_encode($matches[1][$i]).PHP_EOL.PHP_EOL, $markdown);
                }
            }
        }

        // Convert the Markdown text into an HTML text
        $aeConvert = \MarkNotes\Helpers\Convert::getInstance();
        $html = $aeConvert->getHTML($markdown, $params);

        $aeHTML = \MarkNotes\FileType\HTML::getInstance();
        $html = $aeHTML->setBulletsStyle($html);

        // Add the fragment class to any li items when the type for bullet is animated

        if ($aeSettings->getSlideshowBullet() === 'animated') {
            $matches = array();
            preg_match_all('/<li([^>])*(.*)<\/li>/', $html, $matches);

            $j = count($matches[0]);
            for ($i = 0; $i < $j; $i++) {
                $html = str_replace($matches[0][$i], '<li '.$matches[1][$i].' class="fragment"'.$matches[2][$i], $html);
            }
        }
        // Add a data-transition based on the heading : zoom for h1, concave for h2, ...
        // Every heading will be put in a section (i.e. a slide)

        $matches = array();
        preg_match_all('|<h[^>]+>(.*)</h[^>]+>|iU', $html, $matches);

        // Retrieve the animations between slides in the settings.json
        $arrAnimations = $aeSettings->getSlideshowAnimations();

        // $matches contains the list of titles (including the tag so f.i. "<h2>Title</h2>"
        foreach ($matches[0] as $tmp) {
            // The tag (like h2)
            $head = substr($tmp, 1, 2);

            // Retrieve the animation between slides (sections)
            $transition = (isset($arrAnimations[$head]) ? $arrAnimations[$head] : 'slide-in');

            if (substr($tmp, 0, 8) === '<h6>@@@@') {
                // Very special tag : create a new section with an image background

                $extraAttributes = $aeSettings->getSlideshowExtraImgAttributes();

                // Add the slide background image
                $image = $extraAttributes.' data-background-image="'.base64_decode(str_replace('</h6>', '', str_replace('<h6>', '', $tmp))).'" ';

                $html = str_replace($tmp, '</section>'.PHP_EOL.PHP_EOL.'<section '.$image.'data-transition="'.$transition.'">', $html);
            } else {
                // No background
                $html = str_replace($tmp, '</section>'.PHP_EOL.PHP_EOL.'<section data-transition="'.$transition.'">'.$tmp, $html);
            } // if (substr($tmp, 0, 8)==='<h2>@@@@')
        } // foreach

        // Be sure there is no empty slide
        foreach ($arrAnimations as $animation) {
            $html = preg_replace('/<section data-transition="'.$animation.'">[\s\n\r]*<\/section>/m', '', $html);
        }
        // -------------------
        // Consider an <hr> (can be <hr   >, <hr  />, ...) as a new slide

        $matches = array();
        preg_match_all('/<hr *\/?>/', $html, $matches);
        foreach ($matches[0] as $tmp) {
            $html = str_replace($tmp, '</section>'.PHP_EOL.PHP_EOL.'<section>', $html);
        }

        if (substr($html, 0, strlen('</section>')) == '</section>') {
            $html = substr($html, strlen('</section>'), strlen($html));
        }
        $html .= '</section>'.PHP_EOL.PHP_EOL;

        return $html;
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
