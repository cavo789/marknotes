<?php

/**
 * Add an edit button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class Editor
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $title = $aeSettings->getText('edit_file', 'Edit', true);

        $aeSession = \MarkNotes\Session::getInstance();
        $file = $aeSession->get('filename');

        $buttons .=
            '<a id="icon_edit" data-task="edit" data-file="'.$file.'" '.
               'title="'.$title.'" href="#">'.
                '<i class="fa fa-pencil-square-o" aria-hidden="true"></i>'.
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
