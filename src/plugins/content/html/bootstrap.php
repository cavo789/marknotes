<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Bootstrap
{
    public static function doIt(&$html = null)
    {

        // Add bootstrap to tables
        $html = str_replace('<table>', '<div class="table-responsive"><table class="table table-striped table-bordered table-hover">', $html);
        $html = str_replace('</table>', '</table></div>', $html);

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        return true;
    }
}
