<?php
/* REQUIRES PHP 7.x AT LEAST */

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

define('JSON_FILE_NOT_FOUND', 'The file [%s] doesn\'t exists (anymore)');

class JSON
{
    protected static $hInstance = null;

    private static $_debug = false;

    public function __construct()
    {
        self::$_debug = false;
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new JSON();
        }
        return self::$hInstance;
    }

    private static function showError(string $param, bool $die = true) : bool
    {
        if (trim($param) !== '') {
            $param .= ' - ';
        }

        $msg = '';

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $msg = $param.'Maximum stack depth exceeded [error code '.JSON_ERROR_DEPTH.']';
                break;

            case JSON_ERROR_STATE_MISMATCH:
                $msg = $param.'Underflow or the modes mismatch [error code '.JSON_ERROR_STATE_MISMATCH.']';
                break;

            case JSON_ERROR_CTRL_CHAR:
                $msg = $param.'Unexpected control character found [error code '.JSON_ERROR_CTRL_CHAR.']';
                break;

            case JSON_ERROR_SYNTAX:
                $msg = $param.'Syntax error, malformed JSON [error code '.JSON_ERROR_SYNTAX.'] '.
                '(be sure file is UTF8-NoBOM and is correct (use jsonlint.com to check validity))';
                break;

            case JSON_ERROR_UTF8:
                $msg = $param.'Malformed UTF-8 characters, possibly incorrectly encoded [error code '.JSON_ERROR_UTF8.']';
                break;

            default:
                $msg = $param.'Unknown error';
                break;
        } // switch (json_last_error())

        if (self::$_debug == true) {
            $msg .= ' <em class="text-info">(called by '.debug_backtrace()[1]['function'].', line '.
               debug_backtrace()[1]['line'].', '.debug_backtrace()[2]['class'].'::'.debug_backtrace()[2]['function'].
               ', line '.debug_backtrace()[2]['line'].')</em>';
        }

        $msg = '<div class="error bg-danger">ERROR - '.$msg.'</div>';

        if ($die === true) {
            die($msg);
        } else {
            echo $msg;
            return true;
        }
    }

    /**
    * Enable or not the debug mode i.e. display additionnal infos in case of errors
    *
    * @param bool $bState TRUE/FALSE
    */
    public function debug(bool $bState)
    {
        self::$_debug = $bState;
    }

    /**
    * json_decode with error handling.  Show error message in case of problem
    *
    * @param  string $fname Absolute filename
    * @param  bool   $assoc [optional] When TRUE, returned objects will be converted into associative arrays.
    * @return type
    */
    public static function json_decode(string $fname, bool $assoc = false)
    {
        if (!file_exists($fname)) {
            $fname = utf8_decode($fname);
        }
        if (!file_exists($fname)) {
            self::showError(str_replace('%s', '<strong>'.$fname.'</strong>', JSON_FILE_NOT_FOUND), true);
        }

        try {
            // Trim() so we're sure there is no whitespace before the JSON content
            $arr = json_decode(trim(file_get_contents($fname)), $assoc);

            if (json_last_error() != JSON_ERROR_NONE) {
                self::showError($fname, false);
                if (self::$_debug) {
                    echo '<pre>'.file_get_contents($fname).'</pre>';
                }
            } // if (json_last_error()!==JSON_ERROR_NONE)
        } catch (Exception $ex) {
            self::showError($ex->getMessage(), true);
        }

        return $arr;
    }

    public static function json_encode($value, int $option = JSON_PRETTY_PRINT) : string
    {
        $return = '';

        try {
            $return = json_encode($value, $option);

            if (json_last_error() !== JSON_ERROR_NONE) {
                self::showError('', false);
                if (self::$debug) {
                    echo '<pre>'.print_r($value, true).'</pre>';
                }
                die();
            } // if (json_last_error()!==JSON_ERROR_NONE)
        } catch (Exception $ex) {
            self::showError($ex->getMessage(), true);
        }

        return $return;
    }

    /**
     * Convert an array into a JSON string.  Append debugging informations.
     *
     * @param array $arrInfos Array with informations, will be converted into a JSON string
     * @param array $arrDebug Array with debugging info, can be empty
     * @param bool  $die      Display and die (true) or return to the calling code (false)
     */
    public static function json_return_info(array $arrInfos, array $arrDebug, bool $die = true)
    {
        header('Content-Type: application/json');

        /*<!-- build:debug -->*/
        if (count($arrDebug) > 0) {
            $arrInfos = array_merge($arrInfos, $arrDebug);
        }
        /*<!-- endbuild -->*/

        if ($die) {
            header('Content-Type: application/json');
        }
        echo self::json_encode($arrInfos);

        if ($die) {
            die();
        }
    }
}
