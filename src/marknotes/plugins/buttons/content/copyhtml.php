<?php

/**
 * Add a docx export button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class CopyHTML
{
    public static function add(&$buttons = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $title = $aeSettings->getText('copy_html', 'Copy the HTML of the note in the clipboard', true);

        $file = $aeSession->get('filename');
        $data = 'index.php?task=display&param='.base64_encode($file);

        $buttons .=
            '<a id="icon_copyhtml" data-task="fnPluginButtonCopyHTML" '.
                'data-clipboard-action="copy" data-clipboard-text="..." '.
                'title="'.$title.'" href="#">'.
                '<i class="fa fa-code" aria-hidden="true"></i>'.
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
