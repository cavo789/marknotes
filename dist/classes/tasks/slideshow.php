<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-03-20T20:12:50.394Z
*/?>
<?php

namespace AeSecureMDTasks;

class SlideShow
{
    public static function run(array $params)
    {

        $aeSettings=\AeSecure\Settings::getInstance();

        if ($params['filename']!=="") {

            $fullname=utf8_decode($aeSettings->getFolderDocs(true).$params['filename']);

            if (!file_exists($fullname)) {
                echo str_replace('%s', '<strong>'.$fullname.'</strong>', $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists'));
                return;
            }

            $markdown=file_get_contents($fullname);

            // ------------------------------------------------------------------
            // Remove <encrypt xxxx> content </encrypt>
            // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
            preg_match_all('/<encrypt[[:blank:]]*[^>]*>([\\S\\n\\r\\s]*?)<\/encrypt>/', $markdown, $matches);

            // Remove the tag prefix
            $prefix=$aeSettings->getPrefixTag();
            $markdown=str_replace($prefix, '', $markdown);

            // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
            if (count($matches[0])>0) {
                $j=count($matches[0]);

                $i=0;

                for ($i; $i<$j; $i++) {
                    $markdown=str_replace($matches[0][$i], '<strong class="confidential">'.$aeSettings->getText('confidential', 'confidential').'</strong>', $markdown);
                }
            }

            //
            // ------------------------------------------------------------------

            // Try to retrieve the heading 1

            preg_match("/# ?(.*)/", $markdown, $matches);
            $pageTitle = (count($matches)>0) ? trim($matches[1]) : '';

            // Be sure that the heading 1 wasn't type like   # MyHeadingOne # i.e. with a final #
            $pageTitle=rtrim($pageTitle, '#');

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

             //
            // --------------------------------------------------------------------------------

            $slideshow=file_get_contents($aeSettings->getTemplateFile('slideshow'));

            $html=str_replace('<!--%SOURCE%-->', $markdown, $slideshow);
            $html=str_replace('<!--%URL%-->', rtrim(\AeSecure\Functions::getCurrentURL(false, false), '/'), $html);
            $html=str_replace('<--%TITLE%-->', $pageTitle, $html);

            if (!\AeSecure\Functions::isAjaxRequest())
            {

                // Return the HTML : display the slideshow

                header('Content-Type: text/html; charset=utf-8');
                echo $html;

            } else { // if (!\AeSecure\Functions::isAjaxRequest())

                // Generate a HTML file on the disk and return its name
                $fnameHTML=str_replace('.md', '_slideshow.html', $fullname);

                if ($handle = fopen($fnameHTML, 'w+')) {
                    fwrite($handle, $html);
                    fclose($handle);
                }

                // And return an URL to that file
                $tmp = str_replace('\\', '/', rtrim(\AeSecure\Functions::getCurrentURL(false, true), '/').str_replace(dirname($_SERVER['SCRIPT_FILENAME']), '', str_replace(DS, '/', $fnameHTML)));

                header('Content-Type: application/json');
                echo json_encode(utf8_encode($tmp), JSON_PRETTY_PRINT);

            } // if (!\AeSecure\Functions::isAjaxRequest())

        } // if ($filename!="")

        return;

    } // function run()

} // class SlideShow
