<?php

/**
 * Add a Copy in the clipboard button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class Clipboard
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

        $title = $aeSettings->getText('copy_clipboard', 'Copy the note&#39;s content, with page layout, in the clipboard', true);

        $buttons .=
            '<a id="icon_clipboard" data-task="clipboard" aria-hidden="true" '.
                'data-clipboard-action="copy" data-clipboard-target="#CONTENT" '.
                'title="'.$title.'" href="#">'.
                '<i class="fa fa-clipboard" aria-hidden="true"></i>'.
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
