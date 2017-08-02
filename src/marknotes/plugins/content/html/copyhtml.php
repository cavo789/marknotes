<?php

/**
 *
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class CopyHTML
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
            "\n<script type=\"text/javascript\" src=\"".$root."/libs/clipboard/clipboard.min.js\"></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/marknotes/plugins/content/html/copyhtml/copyhtml.js\"></script>\n";

        $js .=
            "<script type=\"text/javascript\">".
            "marknotes.message.copy_html_done='".$aeSettings->getText('copy_html_done', 'The HTML of the note has been placed in the clipboard.', true)."';\n".
            "</script>\n";

        if ($aeSettings->getDebugMode()) {
            $js .= "<!-- End for ".__FILE__."-->";
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // The clipboard plugin will add the clipboard.js script to the interface i.e.
        // only when the running task is "main"
        if ($task !== 'main') {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        return true;
    }
}
