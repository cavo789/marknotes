<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Encrypt
{

    /**
     * Scan the $content and search for <encrypt> tags. If found, unencrypt the information
     */
    public static function doIt(&$content = null) : bool
    {
        if (trim($content) === '') {
            return true;
        }

        // --------------------------------------------------------------------------------------------
        //
        // Add a three-stars icon (only for the display) to inform the user about the encrypted feature

        $matches = array();
        // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
        preg_match_all('/<encrypt[[:blank:]]*[^>]*>([\\S\\n\\r\\s]*?)<\/encrypt>/', $content, $matches);

        // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
        if (count($matches[1]) > 0) {
            $j = count($matches[0]);

            $i = 0;

            $aeSettings = \MarkNotes\Settings::getInstance();

            $aeEncrypt = new \MarkNotes\Plugins\Markdown\Encrypt;
            $aeEncrypt->initialize();

            for ($i; $i < $j; $i++) {
                $decrypt = $aeEncrypt->sslDecrypt($matches[1][$i]);

                $icon_stars = '<i class="icon_encrypted fa fa-lock onlyscreen" aria-hidden="true" '.
                'data-encrypt="true" title="'.str_replace('"', '\"', $aeSettings->getText('is_encrypted', 'This information is encrypted in the original file and decoded here for screen display')).'"></i>';
                // This isn't the edit mode : show the lock icon ($icon_stars)
                $content = str_replace($matches[1][$i], $icon_stars.$decrypt.$icon_stars, $content);
            } // for($i;$i<$j;$i++)
        } // if (count($matches[1])>0)

        return true;
    }

    /**
     * Provide additionnal stylesheets
     */
    public static function addCSS(&$css = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/marknotes/plugins/content/html/encrypt/encrypt.css\" />\n";

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        $aeEvents->bind('render.content', __CLASS__.'::doIt');
        return true;
    }
}
