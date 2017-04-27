<?php

/**
 * Add a Login button into the treeview toolbar
 */

namespace MarkNotes\Plugins\Buttons\Treeview;

defined('_MARKNOTES') or die('No direct access allowed');

class Login
{
    public static function add(&$buttons = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $title = $aeSettings->getText('loginform', 'Login form', true);

        // fnPluginTaskLogin is a function defined in /plugins/task/login/assets/login.js
        // data-task="fnPluginTaskLogin" means that marknotes.js will call that function
        $buttons .=
            '<a id="icon_login" data-task="fnPluginTaskLogin" title="'.$title.'" href="#">'.
                '<i class="fa fa-user" aria-hidden="true"></i>'.
            '</a>';

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'login');

        $login = $arrSettings['username'] ?? '';
        $password = $arrSettings['password'] ?? '';

        // If both login and password are empty (will probably be the case on a localhost server),
        // there is no need to add the Login button
        if (($login === '') && ($password === '')) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('add.buttons', __CLASS__.'::add');
        return true;
    }
}
