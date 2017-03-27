<?php

namespace AeSecureMDTasks;

class SlideShow
{
    protected static $_instance = null;

    private $_aeSettings = null;

    public function __construct()
    {

        if (!class_exists('Settings')) {
            include_once dirname(__DIR__).DS.'settings.php';
        }

        $this->_aeSettings=\AeSecure\Settings::getInstance();

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
        $aeSettings=\AeSecure\Settings::getInstance();

        if ($params['filename']!=="") {
            $fullname=utf8_decode($aeSettings->getFolderDocs(true).$params['filename']);

            if (!file_exists($fullname)) {
                echo str_replace('%s', '<strong>'.$fullname.'</strong>', $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists'));
                return;
            }

            include_once dirname(__DIR__).'/filetype/markdown.php';

            // Read the markdown file
            $aeMD=\AeSecure\FileType\Markdown::getInstance();

            // Keep encrypted datas and show them unencrypted
            $params['removeConfidential']='0';

            $markdown=$aeMD->read($fullname, $params);

            // Don't keep the § (tags prefix) for slideshow
            $markdown=str_replace('§', '', $markdown);

            // Try to retrieve the heading 1
            $pageTitle=$aeMD->getHeadingText($markdown, '#');

            // Check if the params array contains a "type" entry and if so, check if that type is valid i.e.
            // mention the name of an existing templates.  "remark" or "reveal" are supported in the version 1.0.7
            // of MarkNotes.
			
			$type='';
			
            if (isset($params['type'])) {
                include_once dirname(dirname(__FILE__)).'/files.php';

                // $type will be initiealized to an empty string if the file wasn't found in the /templates folder
                $type=\AeSecure\Files::sanitizeFileName($params['type']);
                if ($this->_aeSettings->getTemplateFile($type)==='') {
                    $type='';
                }
            }

            if ($type==='') {
                // Get the type from the settings.json file
                $type=$this->_aeSettings->getSlideshow();
            }

            if ($type==='remark') {
                // The slideshow functionnality will be remark

                // Consider that every Headings 2 and 3 should start in a new slide
                // The "remark" library allow indeed to give a name to each slide by just adding "name: NAME" in the markdown string

                // Get every heading 2 (i.e. lines starting with "## TITLE") and heading 3 ("### Subtitle")

                $arrHeading=array('##','###');
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

                $slideshow=file_get_contents($this->_aeSettings->getTemplateFile('remark'));

                // Remarks use markdown and not HTML;
                $slides=$markdown;
            } else { // if ($aeSettings->getSlideshow()==='remark')

                // Convert the Markdown text into an HTML text

                if (!class_exists('Convert')) {
                    include_once 'convert.php';
                }

                $aeConvert=\AeSecure\Convert::getInstance();
                $html=$aeConvert->getHTML($markdown);

                include_once dirname(__DIR__).'/filetype/html.php';
                $aeHTML=\AeSecure\FileType\HTML::getInstance();
                $html=$aeHTML->setBulletsStyle($html);

                // Add the fragment class to any li items
                $matches=array();
                preg_match_all('/<li[^>]*(.*)<\/li>/', $html, $matches);

                foreach ($matches[1] as $tmp) {
                    $html=str_replace($tmp, ' class="fragment"'.$tmp, $html);
                }

                // Add a data-transition based on the heading : zoom for h1, concave for h2, ...
                // Every heading will be put in a section (i.e. a slide)

                $matches=array();
                preg_match_all('|<h[^>]+>(.*)</h[^>]+>|iU', $html, $matches);

                foreach ($matches[0] as $tmp) {
                    $head=substr($tmp, 0, 4);

                    switch ($head) {
                        case '<h1>':
                            $transition='zoom';
                            break;
                        case '<h2>':
                            $transition='concave';
                            break;
                        default:
                            $transition='slide-in fade-out';
                            break;
                    } // switch

                    $html=str_replace($tmp, '</section>'.PHP_EOL.PHP_EOL.'<section data-transition="'.$transition.'">'.$tmp, $html);
                } // foreach

                if (substr($html, 0, strlen('</section>'))=='</section>') {
                    $html=substr($html, strlen('</section>'), strlen($html));
                }
                $html.='</section>'.PHP_EOL.PHP_EOL;

                // The slideshow functionnality will be reveal.js
                $slideshow=file_get_contents($aeSettings->getTemplateFile('reveal'));
                $slides=$html;
            } // if ($aeSettings->getSlideshow()==='remark')
        } // if ($filename!="")

        // $slideshow contains the template : it's an html file with (from the /templates folder)
        // and that file contains variables => convert them

        include_once dirname(__DIR__).'/filetype/html.php';
        $aeHTML=\AeSecure\FileType\HTML::getInstance();

        return $aeHTML->replaceVariables($slideshow, $slides, $params);
    } // function run()
} // class SlideShow
