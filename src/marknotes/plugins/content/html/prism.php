<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Prism
{

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
            "\n<script type=\"text/javascript\" src=\"".$root."/libs/prism/prism.js\" data-manual></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/marknotes/plugins/content/html/prism/prism.js\"></script>\n";

        if ($aeSettings->getDebugMode()) {
            $js .= "<!-- End for ".__FILE__."-->";
        }

        return true;
    }

    /**
     * Provide additionnal stylesheets
     */
    public static function addCSS(&$css = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $css .=
            "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/libs/prism/prism.css\" />\n";

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // Don't load Prism for slideshows since these frameworks already provide such mechanism
        // But well for reveal slideshows
        if (in_array($task, array('remark'))) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        return true;
    }
}
