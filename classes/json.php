<?php
declare(strict_types=1);

/* REQUIRES PHP 7.x AT LEAST */

define('JSON_FILE_NOT_FOUND','The file [%s] doesn\'t exists (anymore)');

class aeSecureJSON {

   protected static $instance = null;
   
   private static $_debug=false;
   
   function __construct() {

      self::$_debug=FALSE;
      return true;

   } // function __construct()
	
   public static function getInstance() {
		if (self::$instance === null) self::$instance = new aeSecureJSON();
		return self::$instance;
	} // function getInstance()
   
   private static function ShowError(string $param, bool $die=TRUE) : bool {

      if(trim($param)!=='') $param.=' - ';
      
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
            $msg=$param.'Syntax error, malformed JSON [error code '.JSON_ERROR_SYNTAX.'] (be sure file is UTF8-NoBOM)';
            break;

         case JSON_ERROR_UTF8:
            $msg=$param.'Malformed UTF-8 characters, possibly incorrectly encoded [error code '.JSON_ERROR_UTF8.']';
            break;

         default:
            $msg=$param.'Unknown error';
            break;
         
      } // switch (json_last_error())
            
      if (self::$_debug==TRUE) $msg .= ' <em class="text-info">(called by '.debug_backtrace()[1]['function'].', line '.debug_backtrace()[1]['line'].', '.debug_backtrace()[2]['class'].'::'.debug_backtrace()[2]['function'].', line '.debug_backtrace()[2]['line'].')</em>';

      $msg='<div class="error bg-danger">ERROR - '.$msg.'</div>';
      
      if ($die===TRUE) {         
         die($msg);
      } else {
         echo $msg;
         return true;
      }

   } // function ShowError()

   /**
    * Enable or not the debug mode i.e. display additionnal infos in case of errors
    * 
    * @param bool $bState TRUE/FALSE
    */
   public function debug(bool $bState) {
      self::$_debug=$bState;
   } // function debug()
   
   /**
    * json_decode with error handling.  Show error message in case of problem
    * 
    * @param string $fname  Absolute filename
    * @param bool $assoc    [optional] When TRUE, returned objects will be converted into associative arrays.
    * @return type
    */
   public function json_decode(string $fname, bool $assoc = false) {

      if (!file_exists($fname)) {         
         self::ShowError(str_replace('%s','<strong>'.$fname.'</strong>',JSON_FILE_NOT_FOUND),TRUE);
      }

      try {
         
         $arr=json_decode(file_get_contents($fname), $assoc);
         
         if (json_last_error()!==JSON_ERROR_NONE) {
            
            self::ShowError($fname,FALSE);
            if (self::$_debug) echo '<pre>'.file_get_contents($fname).'</pre>';  
            die();
            
         } // if (json_last_error()!==JSON_ERROR_NONE)

      } catch (Exception $ex) {
         
         self::ShowError($ex->getMessage(),TRUE);

      }
      
      return $arr;

   } // function json_decode()
   
   public function json_encode($value, int $option = JSON_PRETTY_PRINT) : string {
      
      $return='';

      try {
         
         $return=json_encode($value, $option);
         
         if (json_last_error()!==JSON_ERROR_NONE) {

            self::ShowError('',FALSE);
            if (self::$_debug) echo '<pre>'.print_r($value,true).'</pre>';  
            die();
            
         } // if (json_last_error()!==JSON_ERROR_NONE)

      } catch (Exception $ex) {
         
         self::ShowError($ex->getMessage(),TRUE);

      }
      
      return $return;

   } // function json_encode()

} // class aeSecureJSON