<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Smileys
{

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $js .=
            "<script type=\"text/javascript\" src=\"".$root."/plugins/content/html/smileys/smileys.js\"></script>\n";

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
        // There is no need for adding smileys when the output format is pdf

        if (in_array($task, array('pdf'))) {
            return true;
        }
        
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        return true;
    }
}
