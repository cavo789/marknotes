<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class Timeline
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    } // function __construct()

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Timeline();
        }

        return self::$_instance;
    } // function getInstance()


    public function run(array $params)
    {
    }
}
