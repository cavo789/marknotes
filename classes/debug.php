<?php
declare(strict_types=1);

/* REQUIRES PHP 7.x AT LEAST */

class aeSecureDebug {
	
   /**
    * Return the current URL
    * 
    * @param type $use_forwarded_host
    * @param type $bNoScriptName          If FALSE, only return the URL and folders name but no script name (f.i. remove index.php and parameters if any)
    * @return type string
    */  
   static public function log(string $line, bool $return=false) : string {
      
      $line.=' <em class="text-info">('.debug_backtrace()[1]['class'].'::'.debug_backtrace()[1]['function'].', line '.debug_backtrace()[0]['line'].')</em><br/>';
      
      if($return==true) {
         return $line;
      } else {
         if (DEBUG) echo $line; 
         return '';
      }
   } // function log()
   
} // class aeSecureDebug

?>