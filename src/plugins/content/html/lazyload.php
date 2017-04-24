<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Lazyload
{
    public static function doIt(&$html = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $html = str_replace(
            '<img src="',
            '<img src="'.$root.'/assets/images/blank.png" class="lazyload" data-src="',
            $html
        );
        return true;
    }

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $aeSettings = \MarkNotes\Settings::getInstance();
        $js .= "<script type=\"text/javascript\" src=\"".$root."/libs/lazysizes/lazysizes.min.js\"></script>\n";
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
        // There is no need for lazyload images  when the output format is pdf

        if (in_array($task, array('pdf'))) {
            return true;
        }
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        return true;
    }
}
