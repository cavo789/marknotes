<?php

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

        $css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/plugins/content/html/gtranslate/gtranslate.css\" />\n";

        return true;
    }

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $js .=
            "<script type=\"text/javascript\" src=\"//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit\"></script>\n".
            "<script type=\"text/javascript\">\n".
            "function googleTranslateElementInit() {\n".
            "   new google.translate.TranslateElement({pageLanguage: '".$aeSettings->getLanguage()."', layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL}, 'google_translate_element');\n".
            "}\n".
            "</script>\n";

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // This plugin is not needed when the task is f.i. 'pdf'
        // There is no need for translation when the output format is pdf

        if (in_array($task, array('edit','pdf'))) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        return true;
    }
}
