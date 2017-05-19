<?php

/**
 * Add a Refresh in the clipboard button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class Refresh
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

        $aeSession = \MarkNotes\Session::getInstance();
        $file = $aeSession->get('filename');

        $title = $aeSettings->getText('refresh', 'Refresh', true);

        $buttons .=
            '<a id="icon_refresh" data-task="display" data-file="'.$file.'" '.
                'title="'.$title.'" href="#">'.
                '<i class="fa fa-refresh" aria-hidden="true"></i>'.
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
