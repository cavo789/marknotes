<?php

/**
 * Google Translate plugin for Marknotes
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class GTranslate
{
    /**
     * Inject an element in the HTML
     */
    public static function doIt(&$html = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $div = "<div id=\"google_translate_element\"></div>";

        $html = str_replace('<body>', '<body>'.$div, $html);
        return true;
    }

    /**
     * Provide additionnal css
     */
    public static function addCSS(&$css = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/marknotes/plugins/content/html/gtranslate/gtranslate.css\" />\n";

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

        if ($aeSettings->getDebugMode()) {
            $js .= "\n<!-- Lines below are added by ".__FILE__."-->";
        }

        $js .=
            "\n<script type=\"text/javascript\" src=\"//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit\"></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/marknotes/plugins/content/html/gtranslate/gtranslate.js\"></script>\n";

        if ($aeSettings->getDebugMode()) {
            $js .= "<!-- End for ".__FILE__."-->";
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind() : bool
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // This plugin is needed only for these tasks : main, display and html

        if (!in_array($task, array('main', 'display', 'html'))) {
            return false;
        }

        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'gtranslate');

        // Check if, in the settings, enable_localhost is set to 1 (default value)
        // If set to 0, don't load the plugin on localhost system
        $localhost = $arrSettings['enable_localhost'] ?? 1;

        // localhost is equal to 1 ? Always load the plugin
        $bLoad = ($localhost == 1);

        if (!$bLoad) {
            // Check if we're on localhost, if so, don't load the plugin
            $bLoad = !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1','::1'));
            // Check name too
            if ($bLoad) {
                $bLoad = ($_SERVER['SERVER_NAME'] !== 'localhost');
            }
        }

        if ($bLoad) {
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->bind('display.html', __CLASS__.'::doIt');
            $aeEvents->bind('render.js', __CLASS__.'::addJS');
            $aeEvents->bind('render.css', __CLASS__.'::addCSS');
            return true;
        } else {
            return false;
        }
    }
}
