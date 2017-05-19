<?php

/**
 * Add a Printer in the clipboard button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class Printer
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

        $title = $aeSettings->getText('print_preview', 'Print preview', true);

        $buttons .=
            '<a id="icon_printer" data-task="printer" title="'.$title.'" href="#">'.
                '<i class="fa fa-print" aria-hidden="true"></i>'.
            '</a>';

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('add.buttons', __CLASS__.'::add');
        return true;
    }
}
