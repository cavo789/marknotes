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
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $title = $aeSettings->getText('copy_clipboard', 'Copy the note&#39;s content, with page layout, in the clipboard', true);

        $titlelink = $aeSettings->getText('copy_link', 'Copy the link to this note in the clipboard', true);

        $file = $aeSession->get('filename');
        $data = 'index.php?task=display&param='.base64_encode($file);

        $buttons .=
            '<a id="icon_clipboard" data-task="fnPluginButtonClipboard" '.
                'data-clipboard-action="copy" data-clipboard-target="#CONTENT" '.
                'title="'.$title.'" href="#">'.
                '<i class="fa fa-clipboard" aria-hidden="true"></i>'.
            '</a>'.
            '<a id="icon_link_note" data-task="fnPluginButtonClipboardLinkNote" '.
                'data-clipboard-text="'.$data.'" '.
                'title="'.$titlelink.'" href="#">'.
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
