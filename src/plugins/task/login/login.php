<?php

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Login
{
    /**
     * Return the code for showing the login form and respond to the login action
     */
    public static function getForm(&$html = null) : bool
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $filename = __DIR__.'/assets/login.frm';

        if ($aeFiles->FileExists($filename)) {

            // Get the root URL
            $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

            $form = file_get_contents($filename);

            $form = str_replace('%ROOT%', rtrim($aeFunctions->getCurrentURL(false, false), '/'), $form);

            $form = str_replace('%LOGINFORM%', $aeSettings->getText('loginform', 'Login form'), $form);
            $form = str_replace('%LOGIN%', $aeSettings->getText('login', 'Username'), $form);
            $form = str_replace('%PASSWORD%', $aeSettings->getText('password', 'Password'), $form);
            $form = str_replace('%SIGNIN%', $aeSettings->getText('signin', 'Sign in'), $form);
            $form = str_replace('%CLEAR_CACHE%', $aeSettings->getText('settings_clean', 'Clear cache'), $form);

            $form .=
                "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/plugins/task/login/assets/login.css\">\n".
                "<script type=\"text/javascript\" src=\"".$root."/plugins/task/login/assets/login.js\"></script>\n";

            // Return the form
            $html = $form;
        }

        return true;
    }
    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $js .=
            "<script type=\"text/javascript\">".
            "marknotes.message.incorrect_login='".$aeSettings->getText('login_error', 'Incorrect login, please try again', true)."';\n".
            "marknotes.message.login_success='".$aeSettings->getText('login_success', 'Login successfull', true)."';\n".
            "</script>";

        return true;
    }

    public static function run(&$params = null)
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $login = json_decode(urldecode($aeFunctions->getParam('username', 'string', '', true)));
        $password = json_decode(urldecode($aeFunctions->getParam('password', 'string', '', true, 40)));

        $status = 0;

        // Get the username / password from settings.json
        $arrSettings = $aeSettings->getPlugins('options', 'login');

        $bLogin = ($login === $arrSettings['username']);
        $bPassword = ($password === $arrSettings['password']);

        $status = ($bLogin && $bPassword) ? 1 : 0;

        $aeSession->set('authenticated', $status);

        header('Content-Type: application/json');
        echo json_encode(array('status' => $status));

        return true;
    }
    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $arrSettings = $aeSettings->getPlugins('options', 'login');

        $login = $arrSettings['username'] ?? '';
        $password = $arrSettings['password'] ?? '';

        // If both login and password are empty (will probably be the case on a localhost server),
        // there is no need to add the Login form and logic
        if (($login === '') && ($password === '')) {
            // no login, no password => consider the user has logged in
            $aeSession->set('authenticated', 1);
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('get.form', __CLASS__.'::getForm');
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('run.task', __CLASS__.'::run');
        return true;
    }
}
