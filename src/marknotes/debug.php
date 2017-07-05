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

    public function enable(bool $devMode = false, string $timezone = 'Europe/London') : bool
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

        // Enable the logger
        /*<!-- build:debug -->*/
        if (is_dir(dirname(dirname(__FILE__)).'/libs/monolog/monolog/src/')) {
            $folder = str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME']));
            $folder = rtrim($folder, DS).DS.'tmp'.DS;

            self::$sDebugFileName = $folder.'debug.log';

            // Don't keep previous run
            if (is_file(self::$sDebugFileName)) {
                try {
                    @unlink(self::$sDebugFileName);
                } catch (Exception $e) {
                }
            }

            $output = "[%datetime%] [%channel%] [%level_name%] %message% %context%\n";
            $formatter = new LineFormatter($output, "Y-m-d H:i:s");

            // \Monolog\Logger::DEBUG =  The minimum logging level at which this
            // handler will be triggered (debug is the lowest)
            $streamHandler = new StreamHandler(self::$sDebugFileName, \Monolog\Logger::DEBUG);
            $streamHandler->setFormatter($formatter);

            self::$logger = new \Monolog\Logger('marknotes');
            self::$logger->pushHandler($streamHandler);
            self::$logger::setTimezone(new \DateTimeZone($timezone));
        }
        /*<!-- endbuild -->*/

        return true;
    }

    /**
     * Add an entry in the /tmp/debug.log file
     */
    public function log(string $msg = '', string $method = 'debug', int $deep = 3) : bool
    {
        /*<!-- build:debug -->*/
        if (self::$logger !== null) {

            // Try to keep the log file readable : remove the parent path if present so
            // filenames will be relative
            $folder = dirname(dirname(__FILE__)).DS;
            $msg = str_ireplace($folder, '', $msg);

            if (!in_array($method, array('debug','info','notice','warning','error','critical','alert','emergency'))) {
                $method = 'debug';
            }

            $trace = debug_backtrace();

            $class = ($trace[1]['class'] ?? '').'::'.($trace[1]['function'] ?? '');

            $context[]['caller'] = $class.' line '.$trace[0]['line'];

            if ($deep > 1) {
                // Add the previous caller
                $file = str_ireplace($folder, '', $trace[1]['file'] ?? '');
                $parent = ($trace[2]['class'] ?? $file).'::'.($trace[2]['function'] ?? '');
                $context[]['caller'] = $parent.' line '.($trace[1]['line'] ?? '');
            }

            if ($deep > 2) {
                if (isset($trace[3]['class'])) {
                    // Add the previous caller
                    $file = str_ireplace($folder, '', $trace[2]['file']);

                    $parent = ($trace[3]['class'] ?? $file).'::'.($trace[3]['function'] ?? '');
                    $context[]['caller'] = $parent.' line '.$trace[2]['line'];
                }
            }

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
