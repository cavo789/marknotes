<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace aeSecure;

class Debug
{

    protected static $instance = null;

    private static $enable=false;

    public function __construct()
    {
        self::$enable=false;
        return true;
    } // function __construct()

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Debug();
        }
        return self::$instance;
    } // function getInstance()

    public function enable()
    {

        self::$enable=true;

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
    } // function enable()

    /**
    * Return the current URL
    *
    * @param  type $use_forwarded_host
    * @param  type $bNoScriptName      If FALSE, only return the URL and folders name but no script name (f.i. remove index.php and parameters if any)
    *                               but no script name (f.i. remove index.php and parameters if any)
    * @return type string
    */
    public static function log(string $line, bool $return = false) : string
    {

        $line.=' ('.debug_backtrace()[1]['class'].'::'.debug_backtrace()[1]['function'].
           ', line '.debug_backtrace()[0]['line'].')';

        if ($return==true) {
            return $line;
        } else {
            if (self::$enable) {
                echo $line;
            }
            return '';
        }
    } // function log()

    /**
     * Example : $this->aeDebug->here();  will display something like
     *   "aeSecureDebug::here called by aeSecure::Setup() in C:\Christophe\Sites\aefc\aesecure\helpers\aesecure.php line 769"
     * @return boolean
     */
    public static function here($msg = null, $deep = 3)
    {

      /*<!-- build:debug -->*/

        $pos=0;
        if ($deep<1) {
            $deep=1;
        }

        $debugTrace = debug_backtrace();
        $class='';
        $file='';
        $line='';
        $func='';
        $txt='';

        for ($i=0; $i<$deep; $i++) {
            if (isset($debugTrace[$pos+$i])) {
                $file = isset($debugTrace[$pos+$i]['file']) ? $debugTrace[$pos+$i]['file'] : '';
                $line = isset($debugTrace[$pos+$i]['line']) ? $debugTrace[$pos+$i]['line'] : '';
            }
            if (isset($debugTrace[$pos+$i+1])) {
                $class = isset($debugTrace[$pos+$i+1]['class']) ? $debugTrace[$pos+$i+1]['class'].'::' : '';
                $func  = isset($debugTrace[$pos+$i+1]['function']) ? $debugTrace[$pos+$i+1]['function'].'()' : '';
            }
            if ($line!='') {
                $txt.=($deep>1?'<li>':'').$class.$func.' in&nbsp;'.$file.' line&nbsp;'.$line.($deep>1?'</li>':'');
            }
        } // for

        $txt='<pre>'.__METHOD__.' called by '.($deep>1?'<ol>':'').$txt.($deep>1?'</ol>':'').
            ($msg!=null?'<div style="padding:10px;border:1px dotted;">'.print_r($msg, true).'</div>':'').
            '</pre>';

        echo $txt;

        /*<!-- endbuild -->*/
        return true;
    }
} // class Debug
