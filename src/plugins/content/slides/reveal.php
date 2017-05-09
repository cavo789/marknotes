<?php

namespace MarkNotes\Plugins\Content\Slides;

defined('_MARKNOTES') or die('No direct access allowed');

class Reveal
{
    private static $layout = 'reveal';

    /**
     * Replace a slide with only an image (like below illustrated) by a section with a background image

     *    ---
     *    ![](.images/image.jpg)
     */
    private static function insertSlideBackgroundImage(string $markdown) : string
    {

        // A manual section break (i.e. a new slide) can be manually created in marknotes
        // by just creating, in the note a new line with --- (or -----).
        // Only these characters on the beginning of the line.
        //
        // WARNING : the string should be with LF (linefeed) only and not CRLF

        $newSlide = '\n+^-{3,5}$\n+';
        $imgTag = '\!\[.*\]\((.*)\)$\n';

        $matches = array();
        if (preg_match_all('/'.$newSlide.$imgTag.'/m', $markdown, $matches)) {
            $j = count($matches[0]);
            for ($i = 0; $i <= $j; $i++) {
                if (isset($matches[0][$i])) {
                    $markdown = str_replace($matches[0][$i], PHP_EOL.PHP_EOL.'###### @@@@'.base64_encode($matches[1][$i]).PHP_EOL.PHP_EOL, $markdown);
                }
            }
        }

        return $markdown;
    }

    private static function processMarkDown(string $markdown) : string
    {
        $markdown = self::insertSlideBackgroundImage($markdown);
        return $markdown;
    }

    /**
     * Set the ul/li style to use Font-Awesome
     */
    private static function setBullets(string $html) : string
    {
        // Replace <li></li> but only if they're part of a <ul></ul> i.e. don't modify <li> for <ol>
        // http://stackoverflow.com/a/4835671
        $sReturn = preg_replace_callback(
           "/(<ul>.*<\/ul>)/Ums",
           function ($ol) {
               // The anonymous function requires to declare the $aeSettings class
               $aeSettings = \MarkNotes\Settings::getInstance();
               $arrSettings = $aeSettings->getPlugins('options', 'bootstrap');
               $icon = $arrSettings['bullet'] ?? 'check';
               $extra = $arrSettings['extra_attribute'] ?? '';
               return preg_replace("/(<li(|\s*\/)>)/", "<li><i class='fa-li fa fa-".$icon."' ".$extra."></i>", $ol[1]);
           },
           $html
        );

        return str_replace('<ul>', '<ul class="fa-ul">', $sReturn);
    }

    /**
     * Should bullets be displayed all at once (no animation) or one by one (fragment)
     */
    private static function addBulletAnimation(string $html) : string
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', static::$layout);

        $style = $arrSettings['animation']['bullet'] ?? 'animated';

        if ($style === 'animated') {
            $matches = array();

            preg_match_all('/<li([^>])*(.*)<\/li>/', $html, $matches);

            $j = count($matches[0]);
            for ($i = 0; $i < $j; $i++) {
                $html = str_replace($matches[0][$i], '<li '.$matches[1][$i].' class="fragment"'.$matches[2][$i], $html);
            }
        }

        return $html;
    }

    /**
     * Add animation between slides
     */
    private static function addSlideAnimation(string $html) : string
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', static::$layout);

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

        // Transitions supported by reveal.js
        $arrTransitions = array('slide', 'none', 'fade','slide','convex','concave','zoom');

        // $matches contains the list of titles (including the tag so f.i. "<h2>Title</h2>"
        foreach ($matches[0] as $tmp) {

            // The tag (like h2)
            $head = substr($tmp, 1, 2);

            // Retrieve the animation between slides (sections)
            $transition = $arrSettings['animation'][$head] ?? $transition;

            if ($transition === 'random') {
                // Random => take one transition, randon
                $transition = $arrTransitions[array_rand($arrTransitions, 1)];
            }

            if (substr($tmp, 0, 8) === '<h6>@@@@') {

                // Very special tag : create a new section with an image background

                $extraAttributes = $arrSettings['section']['extra_data_img_attr'] ?? '';

                // Add the slide background image
                $image = $extraAttributes.' data-background-image="'.base64_decode(str_replace('</h6>', '', str_replace('<h6>', '', $tmp))).'" ';

                $html = str_replace($tmp, '</section>'.PHP_EOL.PHP_EOL.'<section '.$image.'data-transition="'.$transition.'">', $html);
            } else {

                // In order to have nice URLs, extract the title (stored in $tmp)
                // $tmp is equal, f.i., to <h2>My slide title</h2>
                $id = $aeFunctions->slugify(strip_tags($tmp));

                // The ID can't start with a figure, remove it if any
                // Remove also . - , ; if present at the beginning of the id
                $id = preg_replace("/^[\d|.|\-|,|;]+/", "", $id);

                // No background
                $html = str_replace($tmp, '</section>'.PHP_EOL.PHP_EOL.'<section id="'.$id.'"  data-transition="'.$transition.'">'.$tmp, $html);
            } // if (substr($tmp, 0, 8)==='<h2>@@@@')
        } // foreach

        // Be sure there is no empty slide
        foreach ($arr as $animation) {
            $html = preg_replace('/<section data-transition="'.$animation.'">[\s\n\r]*<\/section>/m', '', $html);
        }

        return $html;
    }

    /**
     * Consider an <hr> (can be <hr   >, <hr  />, ...) as a new slide
     */
    private static function addSlideHorinzontalRule(string $html) : string
    {
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

    private static function processHTML(string $html) : string
    {
        $html = self::setBullets($html);
        $html = self::addBulletAnimation($html);
        $html = self::addSlideAnimation($html);
        $html = self::addSlideHorinzontalRule($html);
        return $html;
    }

    /**
     * Retrieve the template for the presentation and use it
     */
    private static function addTemplate($html, array $params = array()) : string
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeHTML = \MarkNotes\FileType\HTML::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $template = $aeSettings->getTemplateFile(static::$layout);
        if ($template === '') {
            $aeFunctions->fileNotFound($template);
        }

        $slideshow = file_get_contents($template);

        return $aeHTML->replaceVariables($slideshow, $html, $params);
    }

    /**
     * Build the reveal html presentation
     */
    public static function doIt(&$params = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if ($params['filename'] !== "") {
            $fullname = $aeSettings->getFolderDocs(true).$params['filename'];

            if (!$aeFiles->fileExists($fullname)) {
                $aeFunctions->fileNotFound($fullname);
            }

            // Get the configuration of the Reveal plugin
            $arrSettings = $aeSettings->getPlugins('options', static::$layout);
            $params['layout'] = static::$layout;

            // Read the markdown file
            $aeMarkDown = \MarkNotes\FileType\Markdown::getInstance();

            $markdown = $aeMarkDown->read($fullname, $params);

            // Try to retrieve the heading 1
            $pageTitle = $aeMarkDown->getHeadingText($markdown, '#');

            $markdown = self::processMarkDown($markdown);

            // Convert the Markdown text into an HTML text
            $aeConvert = \MarkNotes\Helpers\Convert::getInstance();

            $html = $aeConvert->getHTML($markdown, $params);

            $html = self::processHTML($html);

            $html = self::addTemplate($html, $params);
        } // if ($params['filename'] !== "")

        // The slideshow is now created, no need to process other slideshow plugins
        $params['stop_processing'] = true;

        // And return the HTML to the caller
        $params['html'] = $html;
        return true;
    }

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $js .=
            "<script type=\"text/javascript\" src=\"".$root."/libs/clipboard/clipboard.min.js\"></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/plugins/content/html/clipboard/clipboard.js\"></script>\n";

        $arr = $aeSettings->getSlideShow();

        // Get settings
        $minutes = intval($arr['duration']['minutes']) ?? 60;
        $barHeight = intval($arr['duration']['bar_height']) ?? 3;
        $hideUnnecessaryThings = intval($arr['HideUnnecessaryThings']) ?? 0;

        $js .=
            "<script type=\"text/javascript\">".
            "marknotes.slideshow = {};\n".
            "marknotes.slideshow.durationMinutes=".$minutes.";\n".
            "marknotes.slideshow.durationBarHeight=".$barHeight.";\n".
            "marknotes.slideshow.hideunnecessarythings=".($arr['HideUnnecessaryThings'] ?? 0).";\n".
            "</script>";

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // Don't attach code if the task is remark
        if (in_array($task, array('remark'))) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.slides', __CLASS__.'::doIt');
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        return true;
    }
}
