<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;

class Debug
{
    protected static $hInstance = null;

    private static $_enable = false;
    private static $logger = null;

    private static $sDebugFileName = '';

    public function __construct()
    {
        self::$_enable = false;
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new Debug();
        }
        return self::$hInstance;
    }

    public function enable(bool $devMode = false) : bool
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

        // Enable the logger (only when DevMode is set)
        /*<!-- build:debug -->*/
        if (($devMode) && (is_dir(dirname(dirname(__FILE__)).'/libs/monolog/monolog/src/'))) {
            $folder = str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME']));
            $folder = rtrim($folder, DS).DS.'tmp'.DS;

            self::$sDebugFileName = $folder.'debug.log';

            $output = "[%datetime%] [%channel%] [%level_name%] %message% %context%\n";
            $formatter = new LineFormatter($output, "Y-m-d H:i:s");

            $streamHandler = new StreamHandler(self::$sDebugFileName, \Monolog\Logger::DEBUG);
            $streamHandler->setFormatter($formatter);

            self::$logger = new \Monolog\Logger('marknotes');
            self::$logger->pushHandler($streamHandler);

            self::log('marknotes - devmode - enabled');
        }
        /*<!-- endbuild -->*/

        return true;
    }

    /**
     * Add an entry in the /tmp/debug.log file
     */
    public function log(string $msg = '', string $method = 'debug') : bool
    {
        /*<!-- build:debug -->*/
        if (self::$logger !== null) {
            if (!in_array($method, array('debug','info','notice','warning','error','critical','alert','emergency'))) {
                $method = 'debug';
            }
            $class = debug_backtrace()[1]['class'].'::'.debug_backtrace()[1]['function'];
            $context['class'] = $class;
            $context['line'] = debug_backtrace()[0]['line'];

            self::$logger->$method($class.' - '.$msg, $context);
        }
        /*<!-- endbuild -->*/

        return true;
    }

    public function here($msg = null, $deep = 3, $return = false) : string
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

        $previous = '';
        for ($i = 0; $i < $deep; $i++) {
            if (isset($debugTrace[$pos + $i])) {
                $file = isset($debugTrace[$pos + $i]['file']) ? $debugTrace[$pos + $i]['file'] : '';
                $line = isset($debugTrace[$pos + $i]['line']) ? $debugTrace[$pos + $i]['line'] : '';
            }
            if (isset($debugTrace[$pos + $i + 1])) {
                $class = isset($debugTrace[$pos + $i + 1]['class']) ? $debugTrace[$pos + $i + 1]['class'].'::' : '';
                $func = isset($debugTrace[$pos + $i + 1]['function']) ? $debugTrace[$pos + $i + 1]['function'].'()' : '';
            }
            if (($line != '') && ($line !== $previous)) {
                $previous = $line;
                $txt .= ($deep > 1?'<li>':'').$class.$func.' in&nbsp;'.$file.' line&nbsp;'.$line.($deep > 1?'</li>':'');
            }
        } // for

        $txt = '<pre style="background-color:yellow;padding:10px">'.__METHOD__.' called by '.($deep > 1?'<ol>':'').$txt.($deep > 1?'</ol>':'').
            ($msg != null?'<div style="padding:10px;border:1px dotted;">'.print_r($msg, true).'</div>':'').
            '</pre>';

        echo $txt;
        /*<!-- endbuild -->*/

        return true;
    }
}
