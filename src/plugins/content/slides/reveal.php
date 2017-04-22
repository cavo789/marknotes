<?php

namespace MarkNotes\Plugins\Content\Slides;

defined('_MARKNOTES') or die('No direct access allowed');

class Reveal
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

            // Don't keep the § (tags prefix) for slideshow
            //$markdown = str_replace($aeSettings->getTagPrefix(), '', $markdown);

            // Try to retrieve the heading 1
            $pageTitle = $aeMarkDown->getHeadingText($markdown, '#');

            $params['layout'] = 'reveal';

            // A manual section break (i.e. a new slide) can be manually created in marknotes by just creating, in the
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

            // Add the fragment class to any li items when the type for bullet is animated

            $arrSettings = $aeSettings->getPlugins('options', 'reveal');

            // PHP 7 Null coalescing operator  (like than isset($arr[...] ? $arr[...] : 'default')
            $style = $arrSettings['animation']['bullet'] ?? 'animated';

            if ($style === 'animated') {
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
            $arr = array(
                'h1' => 'zoom',
                'h2' => 'concave',
                'h3' => 'slide-in',
                'h4' => 'fade',
                'h5' => 'fade',
                'h6' => 'fade');

            // $matches contains the list of titles (including the tag so f.i. "<h2>Title</h2>"
            foreach ($matches[0] as $tmp) {

                // The tag (like h2)
                $head = substr($tmp, 1, 2);

                // Retrieve the animation between slides (sections)
                $transition = $arrSettings['animation'][$head] ?? $transition;

                if (substr($tmp, 0, 8) === '<h6>@@@@') {

                    // Very special tag : create a new section with an image background

                    $extraAttributes = $arrSettings['section']['extra_data_img_attr'] ?? '';

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

            // -----------------------
            // Get the remark template
            $slideshow = file_get_contents($aeSettings->getTemplateFile($params['layout']));

            // $slideshow contains the template : it's an html file with (from the /templates folder)
            // and that file contains variables => convert them
            $aeHTML = \MarkNotes\FileType\HTML::getInstance();

            $html = $aeHTML->replaceVariables($slideshow, $html, $params);
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
