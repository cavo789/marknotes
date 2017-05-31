<?php

/**
 * Add a Clear cache button to the treeview menu
 * The 'eraser' button will be added only when there is something to clear i.e.
 * when
 *    * localStorage property is enabled (i.e. cache on the client side)
 *    * server_session property is enabled (i.e. cache on the server)
 *
 * These properties can be set in the settings.json file, plugins -> options -> optimisation
 *
 * If both are unset (i.e. equals to zero), the 'eraser' button won't be displayed
 */

namespace MarkNotes\Plugins\Buttons\Treeview;

defined('_MARKNOTES') or die('No direct access allowed');

class Clear
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $title = $aeSettings->getText('settings_clean', 'Clear cache', true);

        // fnPluginTaskClearCache is a function defined in /plugins/task/clear/assets/clear.js
        // data-task="fnPluginTaskClearCache" means that marknotes.js will call that function
        $buttons .=
            '<a id="icon_settings_clear" data-task="fnPluginTaskClearCache" title="'.$title.'" href="#">'.
                '<i class="fa fa-eraser" aria-hidden="true"></i>'.
            '</a>';

        return true;
    }
    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'optimisation');

        $localStorage = boolval($arrSettings['localStorage'] ?? false);
        $serverSession = boolval($arrSettings['server_session'] ?? false);

        // If there is no cache (on the client-side with localStorage or on the server side),
        // the Clear cache button isn't needed
        if (($localStorage === false) && ($serverSession === false)) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('add.buttons', __CLASS__.'::add');
        return true;
    }
}
