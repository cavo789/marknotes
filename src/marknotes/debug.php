<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');


class Debug
{
    protected static $_instance = null;

    private static $_enable = false;

    public function __construct()
    {
        self::$_enable = false;
        return true;
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Debug();
        }
        return self::$_instance;
    }

    public function enable()
    {
        self::$_enable = true;

        ini_set("display_errors", "1");
        ini_set("display_startup_errors", "1");
        ini_set("html_errors", "1");
        ini_set("docref_root", "http://www.php.net/");
        ini_set(
            "error_prepend_string",
            "<div style='color:black;'."
            . "'font-family:verdana;border:1px solid red; padding:5px;'>"
        );
        ini_set("error_append_string", "</div>");
        error_reporting(E_ALL);

        return true;
    }

    /**
    * Return the current URL
    *
    * @param  type $use_forwarded_host
    * @param  type $bNoScriptName      If FALSE, only return the URL and folders name but no script name (f.i. remove index.php and parameters if any)
    *                               but no script name (f.i. remove index.php and parameters if any)
    * @return type string
    */
    public function log(string $line, bool $return = false) : string
    {
        $line .= ' ('.debug_backtrace()[1]['class'].'::'.debug_backtrace()[1]['function'].
           ', line '.debug_backtrace()[0]['line'].')';

        if (($return !== true) && (self::$_enable)) {
            echo $line;
        }

        return $line;
    }

    public function here($msg = null, $deep = 3) : string
    {

        /*<!-- build:debug -->*/
        $pos = 0;
        if ($deep < 1) {
            $deep = 1;
        }

        $debugTrace = debug_backtrace();
        $class = '';
        $file = '';
        $line = '';
        $func = '';
        $txt = '';

        for ($i = 0; $i < $deep; $i++) {
            if (isset($debugTrace[$pos + $i])) {
                $file = isset($debugTrace[$pos + $i]['file']) ? $debugTrace[$pos + $i]['file'] : '';
                $line = isset($debugTrace[$pos + $i]['line']) ? $debugTrace[$pos + $i]['line'] : '';
            }
            if (isset($debugTrace[$pos + $i + 1])) {
                $class = isset($debugTrace[$pos + $i + 1]['class']) ? $debugTrace[$pos + $i + 1]['class'].'::' : '';
                $func = isset($debugTrace[$pos + $i + 1]['function']) ? $debugTrace[$pos + $i + 1]['function'].'()' : '';
            }
            if ($line != '') {
                $txt .= ($deep > 1?'<li>':'').$class.$func.' in&nbsp;'.$file.' line&nbsp;'.$line.($deep > 1?'</li>':'');
            }
        } // for

        $txt = '<pre>'.__METHOD__.' called by '.($deep > 1?'<ol>':'').$txt.($deep > 1?'</ol>':'').
            ($msg != null?'<div style="padding:10px;border:1px dotted;">'.print_r($msg, true).'</div>':'').
            '</pre>';

        echo $txt;

        /*<!-- endbuild -->*/
        return true;
    }
}
