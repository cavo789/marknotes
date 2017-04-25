<?php

/**
 * Add a Copy link in the clipboard button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class CopyLink
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

        $aeSession = \MarkNotes\Session::getInstance();
        $file = $aeSession->get('filename');
        $data = 'index.php?task=display&param='.base64_encode($file);

        $title = $aeSettings->getText('copy_link', 'Copy the link to this note in the clipboard', true);

        $buttons .=
            '<a id="icon_link_note" data-task="clipboard_link_note" data-clipboard-text="'.$data.'" '.
                'title="'.$title.'" href="#">'.
                '<i class="fa fa-link" aria-hidden="true"></i>'.
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
