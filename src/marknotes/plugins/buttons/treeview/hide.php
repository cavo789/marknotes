<?php

/**
 * Add a Timeline button into the treeview toolbar
 */

namespace MarkNotes\Plugins\Buttons\Treeview;

defined('_MARKNOTES') or die('No direct access allowed');

class Hide
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $title = $aeSettings->getText('hide_treeview', 'Hide the list with files, give more place to the content', true);

        $buttons .=
            '<a id="icon_hide" data-task="fnPluginTaskHideTreeViewDoIt" '.
                'title="'.$title.'" href="#">'.
                '<i class="fa fa-compress" aria-hidden="true"></i>'.
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
