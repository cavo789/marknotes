<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace AeSecure\FileType;

class HTML
{
    protected static $instance = null;
    private $_aeSettings = null;

    public function __construct()
    {
        $this->_aeSettings=\AeSecure\Settings::getInstance();

        return true;
    } // function __construct()

    public static function getInstance()
    {

        if (self::$instance === null) {
            self::$instance = new HTML();
        }

        return self::$instance;
    } // function getInstance()

    public function getHeadingText(string $html, string $heading = 'h1') : string
    {
        // Try to find a heading 1 and if so use that text for the title tag of the generated page
        $matches=array();
        $title='';

        try {
            preg_match_all('/<'.$heading.'>(.*)<\/'.$heading.'>/', $html, $matches);
            if (count($matches[1])>0) {
                $title=((count($matches)>0)?rtrim(@$matches[1][0]):'');
            }
        } catch (Exception $e) {
        }

        return $title;
    } // function getHeadingText()

    /**
     * Scan the $html string and add an id to each h2 and h3 tags.
     * Used when the note is displayed as an html page.
     *
     * If $addGoTop is set on true, add also an icon for going back to the top
     * of the page
     */
    public function addHeadingsID(string $html, bool $addGoTop = true) : string
    {
        /*
         * Create a table of content.  Loop each h2 and h3 and add an "id" like "h2_1", "h2_2", ... that will then
         * be used in javascript (see https://css-tricks.com/automatic-table-of-contents/)
         */

        $matches=array();
        $arr=array('h2','h3');

        foreach ($arr as $head) {
            try {
                preg_match_all('/<'.$head.'>(.*)<\/'.$head.'>/', $html, $matches);
                if (count($matches[1])>0) {
                    $i=0;

                    $goTop=($addGoTop ? '<a class="btnTop" href="#top"><i class="fa fa-arrow-circle-up" aria-hidden="true"></i></a>' : '');

                    foreach ($matches[1] as $key => $value) {
                        $i+=1;
                        $html=str_replace('<'.$head.'>'.$value.'</'.$head.'>', $goTop.'<'.$head.' id="'.$head.'_'.$i.'">'.$value.'</'.$head.'>', $html);
                    }
                }
            } catch (Exception $e) {
            } // try
        } // foreach

        return $html;
    } // function addHeadingsID()

    /**
     * Set the ul/li style to use Font-Awesome
     */
    public function setBulletsStyle(string $html) : string
    {

        // Add css to bullets
        $html=str_replace('<ul>', '<ul class="fa-ul">', $html);
        $html=str_replace('<li>', '<li><i class="fa-li fa fa-check"></i>', $html);

        return $html;
    } // function setBulletsStyle()

    /**
     * Return variables from the template file and append the html content
     */
    public function replaceVariables(string $template, string $html, array $params = null) : string
    {
        // Write the file but first replace variables
        $template=str_replace('%TITLE%', $this->getHeadingText($html), $template);
        $template=str_replace('%CONTENT%', $html, $template);
        $template=str_replace('%SITE_NAME%', $this->_aeSettings->getSiteName(), $template);
        $template=str_replace('%ROBOTS%', $this->_aeSettings->getPageRobots(), $template);
        $template=str_replace('%ROOT%', rtrim(\AeSecure\Functions::getCurrentURL(true, false), '/'), $template);
        $template=str_replace('%URL%', rtrim(\AeSecure\Functions::getCurrentURL(false, false), '/'), $template);

        if (isset($params['filename'])) {
            $url=rtrim(\AeSecure\Functions::getCurrentURL(false, false), '/').'/'.rtrim($this->_aeSettings->getFolderDocs(false), DIRECTORY_SEPARATOR).'/';
            $urlHTML=$url.str_replace(DIRECTORY_SEPARATOR, '/', \AeSecure\Files::replaceExtension($params['filename'], 'html'));

            $template=str_replace('%VERSION_PDF%', $urlHTML.'?format=pdf', $template);
            $template=str_replace('%VERSION_HTML%', $urlHTML.'?format=html', $template);


            $template=str_replace('%URL_PAGE%', $urlHTML, $template);
        } // if (isset($params['filename']))

        // Perhaps a Google font should be used.

        if (strpos($template, '<!--%FONT%-->')!==false) {
            $sFont=$this->_aeSettings->getPageGoogleFont(true);
            $template=str_replace('<!--%FONT%-->', $sFont, $template);
        }

        // Check if the template contains then URL_IMG tag and if so, retrieve the first image in the HTML string

        if (strpos($template, '%URL_IMG%')!==false) {
            // Retrieve the first image in the html
            $match=array();
            if (preg_match('/<img *src *= *[\'|"]([^\'|"]*)/', $html, $match)) {
                if (count($match)>0) {
                    $urlImg=$match[1];
                }

                $template=str_replace('%URL_IMG%', $urlImg, $template);
            } // if (preg_match)
        } // if (strpos)

        return $template;
    } // function replaceVariables()
} // class Functions
