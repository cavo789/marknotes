<?php

/**
 * Add a Copy in the clipboard button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class Fullscreen
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

        $title = $aeSettings->getText('fullscreen', 'Display the note in fullscreen', true);

        $buttons .=
            '<a id="icon_fullscreen" data-task="fullscreen" title="'.$title.'" href="#">'.
                '<i class="fa fa-arrows-alt" aria-hidden="true"></i>'.
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
