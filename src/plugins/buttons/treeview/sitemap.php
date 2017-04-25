<?php

/**
 * Add a Sitemap button into the treeview toolbar
 */

namespace MarkNotes\Plugins\Buttons\Treeview;

defined('_MARKNOTES') or die('No direct access allowed');

class SiteMap
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $title = $aeSettings->getText('sitemap', 'Get the sitemap', true);

        $buttons .=
            '<a id="icon_sitemap" data-task="sitemap" title="'.$title.'" href="#">'.
                '<i class="fa fa-sitemap" aria-hidden="true"></i>'.
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
