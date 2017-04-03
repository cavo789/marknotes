<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class SlideShow
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    } // function __construct()

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new SlideShow();
        }

        return self::$_instance;
    } // function getInstance()

    public function run(array $params)
    {
        $aeFiles=\MarkNotes\Files::getInstance();
        $aeSettings=\MarkNotes\Settings::getInstance();

        if ($params['filename']!=="") {
            $fullname=$aeSettings->getFolderDocs(true).$params['filename'];

            if (!$aeFiles->fileExists($fullname)) {
                echo str_replace('%s', '<strong>'.$fullname.'</strong>', $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists'));
                return;
            }

            // Read the markdown file
            $aeMarkDown=\MarkNotes\FileType\Markdown::getInstance();

            // Keep encrypted datas and show them unencrypted
            $params['removeConfidential']='0';

            $markdown=$aeMarkDown->read($fullname, $params);

            // Don't keep the § (tags prefix) for slideshow
            $markdown=str_replace($aeSettings->getTagPrefix(), '', $markdown);

            // Try to retrieve the heading 1
            $pageTitle=$aeMarkDown->getHeadingText($markdown, '#');

            // Check if the params array contains a "type" entry and if so, check if that type is valid i.e.
            // mention the name of an existing templates.  "remark" or "reveal" are supported in the version 1.0.7
            // of MarkNotes.

            $type='';

            if (isset($params['type'])) {
                // $type will be initiealized to an empty string if the file wasn't found in the /templates folder
                $type=$aeFiles->sanitizeFileName($params['type']);
                if ($aeSettings->getTemplateFile($type)==='') {
                    $type='';
                }
            }

            if ($type==='') {
                // Get the type from the settings.json file
                $type=$aeSettings->getSlideshowType();
            }

            if (!isset($params['type'])) {
                $params['type']=$type;
            }

            if ($type==='remark') {
                // The slideshow functionnality will be remark

                // Consider that every Headings 2 and 3 should start in a new slide
                // The "remark" library allow indeed to give a name to each slide by just adding "name: NAME" in the markdown string

                // Get every heading 2 (i.e. lines starting with "## TITLE") till heading 6 ("###### Subtitle")

                $arrHeading=array('##','###','####','#####','######');
                foreach ($arrHeading as $head) {
                    $matches=array();

                    preg_match_all("/\\n".$head." (.*)/", $markdown, $matches);

                    if (count($matches)>0) {
                        // Process one by one
                        $j=count($matches[0]);

                        for ($i=0; $i<$j; $i++) {
                            // $matches[0][$i] is f.i. "## TITLE" while $matches[1][$i] is "TITLE"
                            //
                            // remark allow to specify the name of the slide so add a "name:" property in the markdown like this :
                            //
                            //   name: TITLE
                            //   ---
                            //   ## TITLE

                            $markdown=str_replace(
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

                $matches=array();
                preg_match_all('/-{3,5}/', $markdown, $matches);
                foreach ($matches[0] as $tmp) {
                    $markdown=str_replace($tmp, '---', $markdown);
                }

                $slideshow=file_get_contents($aeSettings->getTemplateFile('remark'));

                // Remarks use markdown and not HTML;
                $slides=$markdown;
            } else { // if ($aeSettings->getSlideshowType()==='remark')

                // A manual section break (i.e. a new slide) can be manually created in MarkNotes by just creating, in the
                // note a new line with --- (or -----).  Only these characters on the beginning of the line.

                $newSlide='\n+^-{3,5}$\n+';
                $imgTag='\!\[.*\]\((.*)\)';

                // Replace a slide with only an image (like below illustrated) by a section with a background image
                //    ---
                //    ![](.images/image.jpg)
                //    ---
                //$markdown=preg_replace('/'.$newSlide.'^\!\[\]\((.*)\)$\n^-{3,5}$\n/m', '******', $markdown);

                $matches=array();
                if (preg_match_all('/'.$newSlide.$imgTag.$newSlide.'/m', $markdown, $matches)) {
                    $j=count($matches);
                    for ($i=0; $i<=$j; $i++) {
                        $markdown=str_replace($matches[0][$i], PHP_EOL.PHP_EOL.'###### '.base64_encode($matches[1][$i]).PHP_EOL.'---'.PHP_EOL, $markdown);
                    }
                }
                // Convert the Markdown text into an HTML text
                $aeConvert=\MarkNotes\Helpers\Convert::getInstance();
                $html=$aeConvert->getHTML($markdown, $params);

                $aeHTML=\MarkNotes\FileType\HTML::getInstance();
                $html=$aeHTML->setBulletsStyle($html);

                // Add the fragment class to any li items when the type for bullet is animated

                if ($aeSettings->getSlideshowBullet()==='animated') {
                    $matches=array();
                    preg_match_all('/<li[^>]*(.*)<\/li>/', $html, $matches);

                    foreach ($matches[1] as $tmp) {
                        $html=str_replace($tmp, ' class="fragment"'.$tmp, $html);
                    }
                }

                // Add a data-transition based on the heading : zoom for h1, concave for h2, ...
                // Every heading will be put in a section (i.e. a slide)

                $matches=array();
                preg_match_all('|<h[^>]+>(.*)</h[^>]+>|iU', $html, $matches);

                foreach ($matches[0] as $tmp) {
                    $head=substr($tmp, 0, 4);
                    $image='';
                    $new=$tmp;

                    switch ($head) {
                        case '<h1>':
                            $transition='zoom';
                            break;
                        case '<h2>':
                            $transition='concave';
                            break;
                        case '<h6>':
                            $image='data-background-image="'.base64_decode(str_replace('</h6>', '', str_replace('<h6>', '', $tmp))).'" ';
                            $new='';
                            break;
                        default:
                            $transition='slide-in fade-out';
                            break;
                    } // switch

                    $html=str_replace($tmp, '</section>'.PHP_EOL.PHP_EOL.'<section '.$image.'data-transition="'.$transition.'">'.rtrim($new), $html);
                } // foreach

                // Be sure there is no empty slide
                $html=str_replace('<section data-transition="slide-in fade-out"></section>', '', $html);

                // -------------------
                // Consider an <hr> (can be <hr   >, <hr  />, ...) as a new slide

                $matches=array();
                preg_match_all('/<hr *\/?>/', $html, $matches);
                foreach ($matches[0] as $tmp) {
                    $html=str_replace($tmp, '</section><section>', $html);
                }

                if (substr($html, 0, strlen('</section>'))=='</section>') {
                    $html=substr($html, strlen('</section>'), strlen($html));
                }
                $html.='</section>'.PHP_EOL.PHP_EOL;
                // The slideshow functionnality will be reveal.js
                $slideshow=file_get_contents($aeSettings->getTemplateFile('reveal'));
                $slides=$html;
            } // if ($aeSettings->getSlideshowType()==='remark')
        } // if ($filename!="")

        // $slideshow contains the template : it's an html file with (from the /templates folder)
        // and that file contains variables => convert them

        $aeHTML=\MarkNotes\FileType\HTML::getInstance();
        return $aeHTML->replaceVariables($slideshow, $slides, $params);
    }
}
