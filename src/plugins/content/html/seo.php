<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class SEO
{
    /**
     * Modify the HTML rendering of the note
     */
    public static function doIt(&$content = null)
    {
        if (trim($content) === '') {
            return true;
        }

        $aeSettings = \MarkNotes\Settings::getInstance();

        $arr = $aeSettings->getPlugins();

        $arrKeywords = array();
        if (isset($arr['options'])) {
            if (isset($arr['options']['seo'])) {
                if (isset($arr['options']['seo']['keywords'])) {
                    $arrKeywords = $arr['options']['seo']['keywords'];
                }
            }
        }

        if (count($arrKeywords) > 0) {
            foreach ($arrKeywords as $key) {

                // Replace a word (i.e. the "$key") in the HTML content but never
                // when that word is inside an html tag; so only modify pure text, nor an
                // attribute
                $pattern = '/'.preg_quote($key).'(?!([^<]+)?>)/i';

                // Search f.i. "marknotes" (the "$key") and add the itemprop="keywords"
                // attribute to enforce SEO on this word
                $replacement = '<span class="SEOKeyword"><span itemscope itemtype="http://schema.org/Article"><span itemprop="keywords">'.$key.'</span></span></span>';

                $content = preg_replace($pattern, $replacement, $content);
            }
        }

        return true;
    }

    /**
     * Provide additionnal css
     */
    public static function addCSS(&$css = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/plugins/content/html/seo/seo.css\" />\n";

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
