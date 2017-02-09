<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.4
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-02-07T09:11:36.452Z
*/?>
<?php
/* REQUIRES PHP 7.x AT LEAST */

namespace AeSecure;

define('JSON_FILE_NOT_FOUND', 'The file [%s] doesn\'t exists (anymore)');

class JSON
{

    protected static $instance = null;
   
    private static $debug=false;
   
    public function __construct()
    {

        self::$debug=false;
        return true;
    } // function __construct()
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new JSON();
        }
        return self::$instance;
    } // function getInstance()
   
    private static function showError(string $param, bool $die = true) : bool
    {

        if (trim($param)!=='') {
            $param.=' - ';
        }
      
        $msg='';
      
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $msg=$param.'Maximum stack depth exceeded [error code '.JSON_ERROR_DEPTH.']';
                break;

            case JSON_ERROR_STATE_MISMATCH:
                $msg=$param.'Underflow or the modes mismatch [error code '.JSON_ERROR_STATE_MISMATCH.']';
                break;

            case JSON_ERROR_CTRL_CHAR:
                $msg=$param.'Unexpected control character found [error code '.JSON_ERROR_CTRL_CHAR.']';
                break;

            case JSON_ERROR_SYNTAX:
                $msg=$param.'Syntax error, malformed JSON [error code '.JSON_ERROR_SYNTAX.'] '.
                   '(be sure file is UTF8-NoBOM)';
                break;

            case JSON_ERROR_UTF8:
                $msg=$param.'Malformed UTF-8 characters, possibly incorrectly encoded [error code '.JSON_ERROR_UTF8.']';
                break;

            default:
                $msg=$param.'Unknown error';
                break;
        } // switch (json_last_error())
            
        if (self::$debug==true) {
            $msg .= ' <em class="text-info">(called by '.debug_backtrace()[1]['function'].', line '.
               debug_backtrace()[1]['line'].', '.debug_backtrace()[2]['class'].'::'.debug_backtrace()[2]['function'].
               ', line '.debug_backtrace()[2]['line'].')</em>';
        }

        $msg='<div class="error bg-danger">ERROR - '.$msg.'</div>';
      
        if ($die===true) {
            die($msg);
        } else {
            echo $msg;
            return true;
        }
    } // function showError()

   /**
    * Enable or not the debug mode i.e. display additionnal infos in case of errors
    *
    * @param bool $bState TRUE/FALSE
    */
    public function debug(bool $bState)
    {
        self::$debug=$bState;
    } // function debug()
   
   /**
    * json_decode with error handling.  Show error message in case of problem
    *
    * @param string $fname  Absolute filename
    * @param bool $assoc    [optional] When TRUE, returned objects will be converted into associative arrays.
    * @return type
    */
    public function json_decode(string $fname, bool $assoc = false)
    {

        if (!file_exists($fname)) {
            self::showError(str_replace('%s', '<strong>'.$fname.'</strong>', JSON_FILE_NOT_FOUND), true);
        }

        try {
            $arr=json_decode(file_get_contents($fname), $assoc);
         
            if (json_last_error()!==JSON_ERROR_NONE) {
                self::showError($fname, false);
                if (self::$debug) {
                    echo '<pre>'.file_get_contents($fname).'</pre>';
                }
                die();
            } // if (json_last_error()!==JSON_ERROR_NONE)
        } catch (Exception $ex) {
            self::showError($ex->getMessage(), true);
        }
      
        return $arr;
    } // function json_decode()
   
    public function json_encode($value, int $option = JSON_PRETTY_PRINT) : string
    {
      
        $return='';

        try {
            $return=json_encode($value, $option);
         
            if (json_last_error()!==JSON_ERROR_NONE) {
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
    } // function json_encode()
} // class JSON
