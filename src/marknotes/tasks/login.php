<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class Login
{
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new Login();
        }

        return self::$_Instance;
    }

    public static function run()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $login = json_decode(urldecode($aeFunctions->getParam('username', 'string', '', true)));
        $password = json_decode(urldecode($aeFunctions->getParam('password', 'string', '', true, 40)));

        $status = 0;

        $arr = $aeSettings->getAdminInfos();

        if ($arr !== null) {
            $bLogin = ((isset($arr['login'])) && ($arr['login'] === $login));
            $bPassword = ((isset($arr['password'])) && ($arr['password'] === $password));
            $status = ($bLogin && $bPassword) ? 1 : 0;
        }

        $aeSession->set('authenticated', $status);

        return json_encode(array('status' => $status));
    }
}
