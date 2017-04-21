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
        $aeSettings = \MarkNotes\Settings::getInstance();
        $js .= "<script type=\"text/javascript\" src=\"libs/lazysizes/lazysizes.min.js\"></script>";

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        return true;
    }
}
