<?php

/**
 * Add a Timeline button into the treeview toolbar
 */

namespace MarkNotes\Plugins\Buttons\Treeview;

defined('_MARKNOTES') or die('No direct access allowed');

class Timeline
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $title = $aeSettings->getText('timeline', 'Display notes in a timeline view', true);

        $buttons .=
            '<a id="icon_timeline" data-task="timeline" title="'.$title.'" href="#">'.
                '<i class="fa fa-calendar" aria-hidden="true"></i>'.
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
