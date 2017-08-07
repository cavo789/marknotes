<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes\FileType;

defined('_MARKNOTES') or die('No direct access allowed');

class HTML
{
    protected static $hInstance = null;
    private $_aeSettings = null;

    public function __construct()
    {
        $this->_aeSettings = \MarkNotes\Settings::getInstance();

        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new HTML();
        }

        return self::$hInstance;
    }

    public function getHeadingText(string $html, string $heading = 'h1') : string
    {
        // Try to find a heading 1 and if so use that text for the title tag of the generated page
        $matches = array();
        $title = '';

        try {
            preg_match_all('/<'.$heading.'>(.*)<\/'.$heading.'>/', $html, $matches);
            if (count($matches[1]) > 0) {
                $title = ((count($matches) > 0)?rtrim(@$matches[1][0]):'');
            }
        } catch (Exception $e) {
        }

        return $title;
    }

    /**
     * Scan the $html string and add an id to each h2 and h3 tags.
     * Used when the note is displayed as an html page.
     *
     * If $addGoTop is set on true, add also an icon for going back to the top
     * of the page
     */
    public function addHeadingsID(string $html, bool $addGoTop = false) : string
    {
        /*
         * Create a table of content.  Loop each h2 and h3 and add an "id" like "h2_1", "h2_2", ... that will then
         * be used in javascript (see https://css-tricks.com/automatic-table-of-contents/)
         */

        $matches = array();
        $arr = array('h2','h3');

        foreach ($arr as $head) {
            try {
                preg_match_all('/<'.$head.'>(.*)<\/'.$head.'>/', $html, $matches);
                if (count($matches[1]) > 0) {
                    $i = 0;

                    // Only for headings 2
                    $goTop = (($addGoTop && ($head === 'h2'))? '<a class="btnTop" href="#top"><i class="fa fa-arrow-circle-up" aria-hidden="true"></i></a>' : '');

                    foreach ($matches[1] as $key => $value) {
                        $i += 1;
                        $html = str_replace('<'.$head.'>'.$value.'</'.$head.'>', $goTop.'<'.$head.' id="'.$head.'_'.$i.'">'.$value.'</'.$head.'>', $html);
                    }
                }
            } catch (Exception $e) {
            } // try
        } // foreach

        return $html;
    }

    /**
     * Return variables from the template file and append the html content
     */
    public function replaceVariables(string $template, string $html, array $params = null) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeEvents = \MarkNotes\Events::getInstance();

        // --------------------------------
        // Call content plugins
        $aeEvents->loadPlugins('content', 'html');
        $args = array(&$html);
        $aeEvents->trigger('render.content', $args);
        $html = $args[0];
        // --------------------------------

        $template = str_replace('%LANGUAGE%', $aeSettings->getLanguage(), $template);
        $template = str_replace('%DEBUG%', $aeSettings->getDebugMode(), $template);
        $template = str_replace('%DOCS%', rtrim($aeSettings->getFolderDocs(false), DS), $template);

        $template = str_replace('%TITLE%', $this->getHeadingText($html), $template);
        $template = str_replace('%CONTENT%', $html, $template);
        $template = str_replace('%SITE_NAME%', $aeSettings->getSiteName(), $template);
        $template = str_replace('%ROBOTS%', $aeSettings->getPageRobots(), $template);
        $template = str_replace('%ROOT%', rtrim($aeFunctions->getCurrentURL(true, false), '/'), $template);
        $template = str_replace('%URL%', str_replace(' ', '%20', rtrim($aeFunctions->getCurrentURL(false, false), '/')), $template);

        $template = str_replace('%APP_NAME%', $aeSettings->getAppName(), $template);
        $template = str_replace('%APP_VERSION%', $aeSettings->getAppName(true), $template);

        $template = str_replace('%APP_WEBSITE%', $aeSettings->getAppHomepage(), $template);
        $template = str_replace('%APP_NAME_64%', base64_encode($aeSettings->getAppName()), $template);
        $template = str_replace('%IMG_MAXWIDTH%', $aeSettings->getPageImgMaxWidth(), $template);

        if (strpos($template, '<!--%META_CACHE%-->') !== false) {
            $cache = '';
            if ($aeSettings->getOptimisationUseBrowserCache()) {
                // Define metadata for the cache
                $cache =
                '<meta http-equiv="cache-control" content="max-age=0" />'.PHP_EOL.
                '<meta http-equiv="cache-control" content="no-cache" />'.PHP_EOL.
                '<meta http-equiv="expires" content="0" />'.PHP_EOL.
                '<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />'.PHP_EOL.
                '<meta http-equiv="pragma" content="no-cache" />';
            }
            $template = str_replace('<!--%META_CACHE%-->', $cache, $template);
        }

        if (isset($params['filename'])) {
            $url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
            $urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($params['filename'], 'html'));

            $template = str_replace('%VERSION_HTML%', $aeFiles->replaceExtension($urlHTML, 'html'), $template);
            $template = str_replace('%VERSION_HTML_TITLE%', $aeSettings->getText('action_html', 'View this slideshow like an article'), $template);

            // The PDF format of the note can be retrieved by replacing the .html extension
            // by .pdf
            $URLpdf = $aeFiles->replaceExtension($urlHTML, 'pdf');
            $template = str_replace('%VERSION_PDF%', utf8_encode($URLpdf), $template);
            $template = str_replace('%VERSION_PDF_TITLE%', $aeSettings->getText('action_download', 'Download this file'), $template);

            $template = str_replace('%VERSION_SLIDESHOW%', $aeFiles->replaceExtension($urlHTML, 'slides'), $template);
            $template = str_replace('%VERSION_SLIDESHOW_TITLE%', $aeSettings->getText('action_slideshow', 'View this article like a slideshow'), $template);

            $template = str_replace('%URL_PAGE%', $urlHTML, $template);
        } // if (isset($params['filename']))

        if (strpos($template, '<!--%FONT%-->') !== false) {
            // Perhaps a Google font should be used.
            $sFont = $aeSettings->getPageGoogleFont(true);
            $template = str_replace('<!--%FONT%-->', $sFont, $template);
        }

        // --------------------------------
        // Call content plugins
        $aeEvents->loadPlugins('content', 'html');
        $additionnalJS = '';
        $args = array(&$additionnalJS);
        $aeEvents->trigger('render.js', $args);
        $template = str_replace('<!--%ADDITIONNAL_JS%-->', $args[0], $template);

        $css = '';
        $args = array(&$css);
        $aeEvents->trigger('render.css', $args);
        $template = str_replace('<!--%ADDITIONNAL_CSS%-->', $args[0], $template);

        // --------------------------------
        // Check if the template contains then URL_IMG tag and if so,
        // retrieve the first image in the HTML string

        if (strpos($template, '%URL_IMG%') !== false) {
            // Retrieve the first image in the html
            $urlImg = '';
            $matches = array();
            if (preg_match_all('/<img.*data-src *= *[\'|"]([^\'|"]*)/', $html, $matches)) {
                foreach ($matches as $val) {
                    if (strpos($val[0], '/blank.png') === false) {
                        $template = str_replace('%URL_IMG%', $val[0], $template);
                        break;
                    }
                }
            } // if (preg_match)
        } //if (strpos($template, '%URL_IMG%')!==false)

        // --------------------------------
        // Call content plugins
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->loadPlugins('content', 'html');
        $args = array(&$template);
        $aeEvents->trigger('display.html', $args);
        $template = $args[0];
        // --------------------------------

        return $template;
    }
}
