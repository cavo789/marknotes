<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class ClearCache
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new ClearCache();
        }

        return self::$_instance;
    }

    public static function run()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSession->destroy();

        return json_encode(array('statut'=>'1'), JSON_PRETTY_PRINT);
    }
}
