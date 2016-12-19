<?php

/**
 * Author : AVONTURE Christophe - https://www.aesecure.com
 * 
 * Based on Parsedown.php : https://github.com/erusev/parsedown
 * 
 * Written date : December 14 2016 
 * 
 * PHP Version : This script has been developped for PHP 7+, the script willn't run with PHP 5.x
 * 
 * Put this script in a website root folder (f.i. site /documentation) and create a subfolder called "docs" (see DOC_FOLDER constant).
 * In "docs", create as many subfolders you want and store there your markdown (.md) files.
 * Access to this script with your browser like f.i. http://localhost/documentation/index.php 
 * The script will display the list of all .md files (found in the folder's structure) and, by clicking on a .md file,
 * will display his html output (created on-the-fly), the html version will be saved on the disk. 
 * 
 * History :
 * 
 * 2016-12-19 : Add support for encryption (tag <encrypt>)
 */

// PHP 7 : force the use of the correct type
declare(strict_types=1);

define('DEBUG',TRUE);

// -------------------------------------------------------------------------------------------------------------------------
// 
// Can be overwritten in settings.json

define('OUTPUT_HTML',TRUE);
define('HTML_TEMPLATE',
   '<!DOCTYPE html>'.
   '<html lang="en">'.
      '<head>'.
         '<meta http-equiv="Cache-control" content="public">'.
         '<meta charset="utf-8"/>'.
         '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />'.
         '<meta name="viewport" content="width=device-width, initial-scale=1.0" />'.
         '<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />'.
         '<title>%TITLE%</title>'.
         '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">'.
      '</head>'.
      '<body>'.
         '<div class="container">%CONTENT%</div>'.
      '</body>'.
      '</html>');

// Folder in this application where .md files are stored
define('DOC_FOLDER','docs');

// Default text, english
define('CONFIDENTIAL','confidential');
define('IS_ENCRYPTED','This information is encrypted in the original file and decoded here for screen display');
define('OPEN_HTML','Open in a new window');

//define('EDITOR','C:\Users\avonture_christophe\AppData\Local\Programs\MarkdownPad 2\MarkdownPad2.exe');

set_time_limit(0);

if(!defined('DS')) define('DS',DIRECTORY_SEPARATOR);

class aeSecureFct {
	
   /**
    * Safely read posted variables
    * 
    * @param type $name          f.i. "password"
    * @param type $type          f.i. "string"
    * @param type $default       f.i. "default"
    * @return type
    */
   public static function getParam($name, $type='string', $default='', $base64=false) {
      
      $tmp='';
      $return=$default;
      
      if (isset($_POST[$name])) {
         if (in_array($type,array('int','integer'))) {
            $return=filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_INT);
         } elseif ($type=='boolean') {
            // false = 5 characters
            $tmp=substr(filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING),0,5);
            $return=(in_array(strtolower($tmp), array('on','true')))?true:false;
         } elseif ($type=='string') {
            $return=filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING);    
            if($base64===true) $return=base64_decode($return);
         } elseif ($type=='unsafe') {
            $return=$_POST[$name];            
         }
		 
      } else { // if (isset($_POST[$name]))
     
         if (isset($_GET[$name])) {
            if (in_array($type,array('int','integer'))) {
               $return=filter_input(INPUT_GET, $name, FILTER_SANITIZE_NUMBER_INT);
            } elseif ($type=='boolean') {
               // false = 5 characters
               $tmp=substr(filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING),0,5);
               $return=(in_array(strtolower($tmp), array('on','true')))?true:false;
            } elseif ($type=='string') {
               $return=filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);    
               if($base64===true) $return=base64_decode($return);                 
            } elseif ($type=='unsafe') {
               $return=$_GET[$name];            
            }
         } // if (isset($_GET[$name])) 
				
      } // if (isset($_POST[$name]))
      
      if ($type=='boolean') $return=(in_array($return, array('on','1'))?true:false);
      
      return $return;	   
	  
   } // function getParam()
   
} // class aeSecureFct

class aeSecureFiles {
	
   /**
    * Check if a file exists and return FALSE if not.  Disable temporarily errors to avoid warnings f.i. when the file
    * isn't reachable due to open_basedir restrictions
    * 
    * @param type $filename
    * @return boolean
    */
   static public function fileExists($filename) : bool {
      
      if ($filename=='') return FALSE;
     
      $errorlevel=error_reporting();
      error_reporting(0);

      $wReturn = is_file($filename);

      error_reporting($errorlevel);

      return $wReturn;
    
   } // function fileExists()
   
   /**
    * Check if a file exists and return FALSE if not.  Disable temporarily errors to avoid warnings f.i. when the file
    * isn't reachable due to open_basedir restrictions
    * 
    * @param type $filename
    * @return boolean
    */
   static private function folderExists($folderName) : bool {
      
      if ($folderName=='') return FALSE;

      $errorlevel=error_reporting();
      error_reporting($errorlevel & ~E_NOTICE & ~E_WARNING);

      $wReturn = is_dir($folderName);

      error_reporting($errorlevel);

      return $wReturn;

   } // function folderExists()
   
   /**
    * Recursive glob : retrieve all files that are under $path (if empty, $path is the root folder of the website)
    * 
    * For instance : aeSecureFct::rglob($pattern='.htaccess',$path=$rootFolder); to find every .htaccess files on the server
    * If folders should be skipped : 
    *    aeSecureFct::rglob('.htaccess',$rootFolder,0,array('aesecure','administrator'))
    * 
    * @param type $pattern
    * @param type $path
    * @param type $flags
    * @param type $arrSkipFolder   Folders to skip... (subfolders will be also skipped)
    * @return type
    */
   static public function rglob($pattern='*', $path='', $flags=0, $arrSkipFolder=null) : array {
      
      static $adjustCase=false;
      
      // glob() is case sensitive so, search for PHP isn't searching for php.
      // Here, the pattern will be changed to be case insensitive.
      // "*.php" will be changed to "*.[pP][hH][pP]"
      
      if (($pattern!='') && ($adjustCase==false)) {
         $length = strlen($pattern);
         $tmp=$pattern;
         $pattern='';
         for ($i=0; $i<$length; $i++) {
            $pattern.=(ctype_alpha($tmp[$i]) ? '['.strtolower($tmp[$i]).strtoupper($tmp[$i]).']' : $tmp[$i]);
         }
         // Do this only once
         $adjustCase=true;
      }         
      
      // If the "$path" is one of the folder to skip, ... skip it.
      
      if (($arrSkipFolder!=null) && (count($arrSkipFolder)>0)) {
         foreach ($arrSkipFolder as $folder) {
            if (self::startsWith($path, $folder)) return null;
         } // foreach
         
      } // if (($arrSkipFolder!=null) && (count($arrSkipFolder)>0))
      
      $paths=glob($path.'*', GLOB_MARK|GLOB_ONLYDIR);
      $files=glob(rtrim($path,DS).DS.$pattern, $flags);
      
      foreach ($paths as $path) {
         
         if( self::folderExists($path)) { 

            // Avoid recursive loop when the folder is a symbolic link
            if (rtrim(str_replace('/',DS,$path),DS)==realpath($path)) { 
               $arr=self::rglob($pattern, $path, $flags, $arrSkipFolder);
               if (($arr!=null) && (count($arr)>0)) $files=array_merge($files,$arr);
            } else {
               // $path is a symbolic link.  Doing a glob on a symbolic link will create a recursive
               // call and will crash the script
            }
            
         } // if(!(is_link($path))) {
         
      } // foreach
      
      // Don't use the / notation but well the correct directory separator
      foreach ($files as $key=>$value) $files[$key]=str_replace('/',DS,$value);
	  
      @sort($files);
	  
      return $files;
	  
   } // function rglob()
   
   static public function replace_extension($filename, $new_extension) : string {
      $info = pathinfo($filename);
      return dirname($filename).DS.$info['filename'].'.'.$new_extension;
   }
   
} // class aeSecureFiles 

/**
 * Encryption class.  Use SSL for the encryption.  
 * 
 * Partially based on @link : http://php.net/manual/fr/function.openssl-decrypt.php#111832
 */
class aeSecureEncrypt {
   
   private $_method='';
   private $_password='';
   private $_iv='';
   
   /**
    * Initialize the class
    * 
    * @param string $password  The password to use for the encryption
    * @param string $method    OPTIONAL, If not mentionned, will be 'aes-256-ctr'
    */
   function __construct(string $password, string $method) {
      
      $this->_password=$password;
      
      if (trim($method)==NULL) {
         $this->_method='aes-256-ctr';
      } else {      
         $this->_method=$method;
      }

      // Dynamically generate an "IV" i.eI and initialization vector that will ensure cypher to be unique 
      // (http://stackoverflow.com/questions/11821195/use-of-initialization-vector-in-openssl-encrypt)
      // And concatenate that "IV" to the encrypted texte
      
      $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
      $this->_iv= mcrypt_create_iv($iv_size, MCRYPT_RAND);;

   } // function __construct()   
   
   /**
    * Encrypt a string by using SSL
    * 
    * @param string $data      The string that should be encrypted
    * @param string $password  OPTIONAL, If not mentionned, use the password define during the class initialization
    * @param string $method    OPTIONAL, If not mentionned, will be 'aes-256-ctr'
    * @return type             The string, encrypted.  The first characters will contains the "Initialization vector", required for the decryption
    */
   public function sslEncrypt(string $data, $password) : string {
      
      if ($password===NULL) $password=$this->_password;
      
      if(function_exists('openssl_encrypt')) {
         return urlencode($this->_iv.openssl_encrypt(urlencode($data), $this->_method, $password, 0, $this->_iv));
      } else {
         return urlencode($this->_iv.exec("echo \"".urlencode($data)."\" | openssl enc -".urlencode($this->_method)." -base64 -nosalt -K ".bin2hex($password)." -iv ".bin2hex($this->_iv)));
      }
      
   } // function sslEncrypt()
   
   /**
    * Decrypt a SSL encrypted string
    * 
    * @param string $data   The string to decrypt.  The first characters contains the "Initialiation vector"
    * @param string $password  OPTIONAL, If not mentionned, use the password define during the class initialization
    * @return type          The string, decrypted
    */
   public function sslDecrypt(string $data, $password) : string {
  
      if ($password===NULL) $password=$this->_password;
      
      $tmp=urldecode($data);
      
      $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
      $iv = substr($tmp, 0, $iv_size);

      if(function_exists('openssl_decrypt')) {
         return trim(urldecode(openssl_decrypt(substr($tmp, $iv_size), $this->_method, $password, 0, $iv)));
      } else {
        return trim(urldecode(exec("echo \"".$tmp."\" | openssl enc -".$this->_method." -d -base64 -nosalt -K ".bin2hex($password)." -iv ".bin2hex($this->_iv))));
      }

   } // function sslDecrypt()
   
} // class aeSecureEncrypt

class aeSecureMarkdown {
   
   private $_json=null;                // JSON, contains the content of the settings.json file

   private $_rootFolder='';            // root folder of the web application (f.i. "C:\Christophe\Documents\")
   
   private $_settingsDocsFolder='';    // subfolder f.i. 'docs' where markdown files are stored (f.i. "Docs\")
   private $_settingsLanguage='';      // user's language
   
   private $_encryptionMethod='';      // Method to use for the encryption
   private $_encryptionPassword='';    // Password for the encryption / decryption
   
   private $_saveHTML=TRUE;            // When displaying a .md file, generate and store its .html rendering
   private $_htmlTemplate='';          // Template to use for the exportation
   
	private $_langOPEN_HTML='';         // Translation for "Open in a new window"
   private $_langISENCRYPTED='';       // Translation for "This information is encrypted in the original file"
   
   /**
    * Class constructor : initialize a few private variables
    * 
    * @param string $folder    Root folder of the website (f.i. "C:\Christophe\Documents\").
    * @return boolean
    */
   function __construct(string $folder) {
      
      // Get the root folder and be sure the folder ends with a slash
      $this->_rootFolder=rtrim($folder,DS).DS;
      
      // Initialize with default values
      $this->_settingsDocsFolder=''; //DOC_FOLDER.DS;
      $this->_settingsLanguage='en';
      $this->_htmlTemplate=HTML_TEMPLATE;
      $this->_saveHTML=OUTPUT_HTML;
      
      $this->_langCONFIDENTIAL=CONFIDENTIAL;
      $this->_langISENCRYPTED=IS_ENCRYPTED;
      $this->_langOPEN_HTML=OPEN_HTML;
      
      // No password defined by default
      $this->_settingsPassword='';
      
      // Read the user's settings
      self::ReadSettings();

      return true;

   } // function __construct()
   
   /**
    * Read the user's settings i.e. the file "settings.json"
    * Initialize class properties 
    */
   private function ReadSettings() : bool {
     
      // Process the settings.json file
      if (aeSecureFiles::fileExists($fname=$this->_rootFolder.'settings.json')) {
   
         $this->_json=json_decode(file_get_contents($fname),true);

         // Retrieve the subfolder if any
         if(isset($this->_json['folder']))   $this->_settingsDocsFolder=rtrim($this->_json['folder'],DS).DS;
         if(isset($this->_json['language'])) $this->_settingsLanguage=$this->_json['language'];
         
         // Retrieve the password if mentionned
         if(isset($this->_json['password'])) $this->_settingsPassword=$this->_json['password'];
         
         // Get export settings
         if(isset($this->_json['encryption'])) {
            $tmp=$this->_json['encryption'];
            if(isset($tmp['password'])) $this->_encryptionPassword=$tmp['password'];
            if(isset($tmp['method']))   $this->_encryptionMethod=$tmp['method'];
         }
         
         // Get export settings
         if(isset($this->_json['export'])) {
            $tmp=$this->_json['export'];
            if(isset($tmp['save_html'])) $this->_saveHTML=$tmp['save_html'];
            if(isset($tmp['html']))      $this->_htmlTemplate=$tmp['html'];
         }
         
         // Get translations
         if(isset($this->_json['languages'][$this->_settingsLanguage])) {
            $tmp=$this->_json['languages'][$this->_settingsLanguage];
            if(isset($tmp['confidential'])) $this->_langCONFIDENTIAL=$tmp['confidential'];
            if(isset($tmp['encrypted'])) $this->_langISENCRYPTED=$tmp['encrypted'];
            if(isset($tmp['open_html'])) $this->_langOPEN_HTML=$tmp['open_html'];
         }
         
      }  
      return TRUE;
      
   } // function ReadSettings()
   
   /**
    * Get the list of .md files.  This list will be used in the "table of contents"
    * 
    * @return string
    */   
   public function ListFiles() : string {
	   	  
      $arrFiles=array_unique(aeSecureFiles::rglob('*.md',$this->_rootFolder.$this->_settingsDocsFolder));

      // Be carefull, folders / filenames perhaps contains accentuated characters
      $arrFiles=array_map('utf8_encode', $arrFiles);
      
      // Sort, case insensitve
      natcasesort($arrFiles);   

      $sReturn = '<h5>'.$this->_rootFolder.$this->_settingsDocsFolder.'</h5>'.
         '<table id="tblFiles" class="table tablesorter table-hover table-bordered table-striped">'.
         '<thead>'.
            '<tr>'.
               '<td data-placeholder="Filter on a folder" class="filter-select filter-exact ext">Folder</td>'.
               '<td data-placeholder="Search for a filename..."  class="filter-match">Filename</td>'.
            '</tr>'.
         '</thead>'.
         '<tbody>';

      foreach ($arrFiles as $file) {
         // Don't mention the full path, should be relative for security reason
         $file=str_replace($this->_rootFolder.$this->_settingsDocsFolder,'',$file);
         
         $folder=(trim(dirname($file))=='.')?'(root)':dirname($file);
         
         $sReturn.='<tr><td data-folder="'.$folder.'">'.$folder.'</td><td data-file="'.$file.'">'.str_replace('.md','',basename($file)).' <span class="edit">(edit)</span></td></tr>';
      }
      
      $sReturn.='</tbody></table>';

      return $sReturn;
	  
   } // function ListFiles()	  
  
   /**
    * Return the HTML rendering of a .md file
    * 
    * @param type $filename   Relative filename of the .md file to open and display
    * @return string          HTML rendering 
    */ 
   public function ShowFile($filename) : string {
	   
      $fullname=utf8_decode($this->_rootFolder.$this->_settingsDocsFolder.$filename);
      
      $markdown=file_get_contents($fullname);
      
      $icons='';

      // Check if there are <encrypt> tags.  If yes, check the status (encrypted or not) and retrieve its content
      $matches = array();
      preg_match_all('/<encrypt[[:blank:]]*([^>]*)>(.*?)<\/encrypt>/', $markdown, $matches);

      // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
      if (count($matches[1])>0) {
         
         $icon_stars='<span class="encrypted onlyscreen" data-encrypt="true" title="'.str_replace('"','\"',$this->_langISENCRYPTED).'">&nbsp;</span>';
         
         // Initialize the encryption class
         $aesEncrypt=new aeSecureEncrypt($this->_encryptionPassword, $this->_encryptionMethod);
         
         $j=count($matches[0]);
               
         $i=0;
         
         $rewriteFile=FALSE;
     
         // Loop and process every <encrypt> tags
         // For instance : <encrypt data-encrypt="true">ENCRYPTED TEXT</encrypt>

         for($i;$i<$j;$i++) {
                        
            // Retrieve the attributes (f.i. data-encrypt="true")            
            $attributes=$matches[1][$i];
            
            $isEncrypted=FALSE;
            
            $tmp=array();
            preg_match('#data-encrypt="(.*)"#', $attributes, $tmp);
            
            if(count($tmp)>0) {
               // Only when data-encrypt="true" is found, consider the content has an encrypted one.
               $isEncrypted=(strcasecmp(rtrim($tmp[1]),'true')===0?TRUE:FALSE);
            }

            // Retrieve the text (encrypted if data-encrypt was found and set on "true"; uncrypted otherwise)
            $words=$matches[2][$i];

            // If we need to crypt we a new password, 
            // NULL = try to use the current password, defined in the settings.json file
            //$decrypt=$aesEncrypt->sslDecrypt($words,NULL);
            //if (!ctype_print($decrypt)) {  // ctype_print will return FALSE when the string still contains binary info => decrypt has failed
            //   $words=$aesEncrypt->sslDecrypt($words,'');
            //   $isEncrypted=FALSE;
            //}
            
            if (!$isEncrypted) {
               
               // At least one <encrypt> tag found without attribute data-encrypt="true" => the content should be encrypted and
               // the file should be override with encrypted data
               
               $encrypted=$aesEncrypt->sslEncrypt($words,NULL);              

               $markdown=str_replace($matches[0][$i], utf8_encode('<encrypt data-encrypt="true">'.$encrypted.'</encrypt>'), $markdown);
               
               $rewriteFile=TRUE;

            } // if (!$isEncrypted)
            
         } // for($i;$i<$j;$i++)         

         if ($rewriteFile===TRUE) {
            
            // The file has been changed => there was information with an <encrypt> tag but not yet encrypted.
            // Now, $markdown contains only encrypted <encrypt> tag (i.e. with data-encrypt="true" attribute)

            rename($fullname, $fullname.'.old');
            
            try {
               
               if ($handle = fopen($fullname,'w')) {
                  fwrite($handle, $markdown);
                  fclose($handle);		
               }               
               
               if (filesize($fullname)>0) unlink($fullname.'.old');
               
            } catch (Exception $ex) {               
            }

         } // if ($rewriteFile===TRUE)

         // --------------------------------------------------------------------------------------------
         // 
         // Add a three-stars icon (only for the display) to inform the user about the encrypted feature
         
         $matches = array();
         preg_match_all('/<encrypt[[:blank:]]*[^>]*>(.*?)<\/encrypt>/', $markdown, $matches);

         // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
         if (count($matches[1])>0) {
            
            $j=count($matches[0]);

            $i=0;
         
            for($i;$i<$j;$i++) {

               // Great, the info is already encrypted
               
               $icons='<div class="lock_green">&nbsp;</div>';
               
               $decrypt=$aesEncrypt->sslDecrypt($matches[1][$i],NULL);

               $markdown=str_replace($matches[1][$i], $icon_stars.$decrypt.$icon_stars, $markdown);
               
            }

         }
         
         // Release 
         
         unset($aesEncrypt);
         
      } // if (count($matches[1])>0)

      require_once("libs/Parsedown.php");
      $Parsedown=new Parsedown();      
      $html=$Parsedown->text($markdown);
      
      // Check if the .html version of the markdown file already exists; if not, create it 
      if ($this->_saveHTML===TRUE) {

         $fnameHTML=aeSecureFiles::replace_extension($fullname,'html');

         if (is_writable(dirname($fullname).DS)) {

            // If the file already exists check his version (md5) against the new content : replace the file if not the latest version
            if(file_exists($fnameHTML)) {
               $md5=md5_file($fnameHTML);
               if ($md5!==md5($html)) @unlink($fnameHTML);
            }

            if (!file_exists($fnameHTML)) {
               
               $tmp=$html;
               
               // Don't save unencrypted informations
               $matches = array();
               preg_match_all('/<encrypt[[:blank:]]*[^>]*>(.*?)<\/encrypt>/', $tmp, $matches);

               // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
               if (count($matches[0])>0) {

                  $j=count($matches[0]);

                  $i=0;

                  for($i;$i<$j;$i++) {
                     $tmp=str_replace($matches[0][$i], '<strong class="confidential">'.$this->_langCONFIDENTIAL.'</strong>', $tmp);
                  }
               }


               if ($handle = fopen($fnameHTML,'w+')) {

                  // Try to find a heading 1 and if so use that text for the title tag of the generated page
                  $matches=array();
                  try {
                     preg_match_all('/<h1>(.*)<\/h1>/', $tmp, $matches);
                     if(count($matches[1])>0) {
                        $title=((count($matches)>0)?rtrim(@$matches[1][0]):'');  
                     } else {
                        $title='';
                     }
                  } catch(Exception $e){    
                  }

                  // Write the file but first replace variables
                  $tmpl=str_replace('%TITLE%',$title,$this->_htmlTemplate);
                  $tmpl=str_replace('%CONTENT%',$tmp,$tmpl);
                  
                  fwrite($handle,$tmpl);

                  fclose($handle);		

               } // if ($handle = fopen($fname,'w+'))

             } // if (!file_exists($fname))

		 } // if (is_writable(dirname($fname)))

      } // if (OUTPUT_HTML===TRUE)

      $fnameHTML=dirname($_SERVER['HTTP_REFERER']).str_replace(str_replace('/',DS,dirname($_SERVER['SCRIPT_FILENAME'])),'',$fnameHTML);
      
      $html=str_replace('</h1>', '</h1><div id="icons">'.$icons.'</div>',$html);
      $html=str_replace('src="images/', 'src="'.DOC_FOLDER.'/'.str_replace(DS,'/',dirname($filename)).'/images/',$html);
      $html=str_replace('href="files/', 'href="'.DOC_FOLDER.'/'.str_replace(DS,'/',dirname($filename)).'/files/',$html);
      $html='<h5 class="onlyscreen filename">'.utf8_encode($fullname).'</h5>'.
         (OUTPUT_HTML===TRUE ? '<div class="onlyscreen"><a href="'.utf8_encode($fnameHTML).'" style="text-decoration:underline;" target="_blank">'.OPEN_HTML.'</a></div>' : '').
         $html.'<hr/>';

      return $html;
	  
   } // function ShowFile()
   
   /*public static function EditFile($filename) {
	   
      $cmd=EDITOR.' '.escapeshellarg(trim($filename)); 

      if (substr(php_uname(), 0, 7) == "Windows") pclose(popen("start /B ". $cmd, "r"));

      return true;

   } // function EditFile()*/
   
} // class aeSecureMarkdown


   if (DEBUG===TRUE) {
      ini_set("display_errors", "1");
      ini_set("display_startup_errors", "1");
      ini_set("html_errors", "1");
      ini_set("docref_root", "http://www.php.net/");
      ini_set("error_prepend_string", "<div style='color:black;font-family:verdana;border:1px solid red; padding:5px;'>");
      ini_set("error_append_string", "</div>");
      error_reporting(E_ALL);
   } else {	   
      ini_set('error_reporting', E_ALL & ~ E_NOTICE);	  
   }
   
   $task=aeSecureFct::getParam('task','string','',false);

   // Create an instance of the class and initialize the rootFolder variable (type string)
   $aeSMarkDown = new aeSecureMarkdown((string) str_replace('/',DS,dirname($_SERVER['SCRIPT_FILENAME'])).DS);
   
   switch ($task) {
      
      /*case 'editFile':
         $fname=json_decode(urldecode(base64_decode(aeSecureFct::getParam('param','string','',false))));
         die(aeSecureMarkdown::EditFile($fname));*/
         
      case 'display':
         
         header('Content-Type: text/html; charset=utf-8'); 
         $fname=json_decode(urldecode(base64_decode(aeSecureFct::getParam('param','string','',false))));
         $result=$aeSMarkDown->ShowFile($fname);
         echo $result;
         unset($aeSMarkDown);
         die();
         
      case 'listFiles':
         
         // Retrieve the list of .md files.   
         // Default task
         
         echo $aeSMarkDown->ListFiles();
         unset($aeSMarkDown);
         die();
		 
   } // switch ($task)
   
   unset($aeSMarkDown);

?>

<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="utf-8"/>
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <meta name="robots" content="noindex, nofollow" />
      <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" /> 
      <meta http-equiv="cache-control" content="max-age=0" />
      <meta http-equiv="cache-control" content="no-cache" />
      <meta http-equiv="expires" content="0" />
      <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
      <meta http-equiv="pragma" content="no-cache" />
      <title>aeSecure - Docs</title>
      <link href= "data:image/x-icon;base64,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" rel="shortcut icon" type="image/vnd.microsoft.icon"/>  
	  
      <?php 
         if (file_exists($fname='libs/bootstrap.min.css')) {
            echo '<link href="'.$fname.'" rel="stylesheet">';
         } else {
            echo '<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">';
         }
         if (file_exists($fname='libs/theme.ice.min.css')) {
            echo '<link href="'.$fname.'" rel="stylesheet">';
         } else {
            echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.25.3/css/theme.ice.min.css" rel="stylesheet" media="screen" />';
         }
       ?>

      <style>

         .selected{background-color:#90b6e2 !important;color:white !important;}
         .showFileList{color:blue;text-decoration:underline;cursor:pointer;}
         .filename{font-style:italic;font-weight:bold;}
         .edit{font-size:smaller;display:none;}
         
         #files{background-color:#f9f2db;padding:15px 0px 15px 40px;border:5px solid white;}
         #files li{display:block;min-width:74px;text-decoration:underline;cursor:pointer;}
         #files li:before{content:"\e022";font-family:'Glyphicons Halflings';font-size:9px;float:left;margin-top:4px;margin-left:-17px;color:#CCCCCC;}

         #icons {display:inline-block;position:absolute;top:5px;right:-1px;margin-right:10px;}
         
         body{background:rgb(204,204,204);}
         page{background:white;display:block;margin:0 auto;margin-bottom:0.5cm;box-shadow:0 0 0.5cm rgba(0,0,0,0.5);}
         
         /* Images */
         .encrypted {background-repeat:no-repeat;display:inline-block;height:16px;width:48px;background-image:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAQCAYAAABQrvyxAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAUBJREFUeNrsVdENgjAQReMAuEFHYANhAnECIekAOoE6AQ7QDzbQDawbMAIjdAM92qsgKbWYGn94yTWBvh7vjtc2CCZM+AMY20GEDrxIct1yppI/EvMvxGcwFhAHB3Yhg7H4Q04C4wXjxwW0wjPrX1Ci496aTzkJNsgZM8euN52MMPqiOYSAyKUAJSbsiNeoMe4BpUcUusU1pMetMOceuJVN2sKx0MwgXCPGj+n5dICnhQp8XhuK1Ig6ayofFkqweyYIOU9pjd3KLXnaeUo3MJYWbg6cqx8LtZ6+GWaa33w2WM50+iyBK3rch4HHgZf43sRk4H1osYD9vTp9XHN6K6CWXVebt8HKIoAjVwwIIx0bnjqWcr4PFiMKKKV4SvVHzvLyacW920r5nGOnSzwI+MCeuL6sxdjJsrknTPCNpwADAM/IW54Td+BPAAAAAElFTkSuQmCC");}
         /*.lock_orange{background-repeat:no-repeat;display:inline-block;height:64px;width:64px;background-image:url("data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBwgHEgkIBxQKFRMLGBsaGBcYGBUaHRcdIBciICAeHRUZHSknHR0lGxcfITEhJzUrLzouGB8zODMtNyotLi0BCgoKDg0OGxAQGy0jHyIyNy43MDcrLisrMjcrNy03Nys3Ny0tLS84Njc3Ky8rLzUtKysrLTU4Ly0tLS8uLTAtLf/AABEIAEAAQAMBEQACEQEDEQH/xAAbAAACAwEBAQAAAAAAAAAAAAAABQIEBgEDB//EADAQAAEDAgQCCQMFAAAAAAAAAAEAAgMEEQUhQXESwQYTQlGBkaGx4RQyMzFSYWLw/8QAGgEAAwADAQAAAAAAAAAAAAAAAAQFAQMGAv/EACwRAAICAQICCgIDAQAAAAAAAAABAgMEERIFMRMhMjNBUWFxgbGRwSI00SP/2gAMAwEAAhEDEQA/APuKABAFCvr+pvHDa+p7vlTMzP6N7Ic/oapo3dcuQqd9RUXuZHeZ9FK33WvxY6tkPJEQaimIsZG+Y9EKd1T8UZ/hNeDGuH4j1xEU9uLQ9/yq2JndI9k+f2JX4+3+UeQxVIUBAAgDyqZOpa+TuC05FnR1Sn5HuuO6SQlpYTUvDTfvK53Gpd9u1+7KNk9kdR7GxsYDWAABdNCEYLbFaImSk5PVhJGyUFkgBBROEZrbJaoIycXqjOV0BpHljb94K5vIpdFu1e6K1M+khqP6Obr2RyfuHqugos6StS8yZbDZNxPZbjWCAKmJ/jf4e6S4j/Xfx9m/H7xFTBvuk25pDhXeS9jfl9lDJ80UeT3MG5CsSurj1Skl8iahJ8kDJ4pMmOYdiERurl1Rkn8g4SXNCjHvuj25qRxTtx9h/C7LL2D/AIo/H3T2B3C+RbK71l1Oi4IAW43UtiaItX+wUni2RGutQ8X+hvErcpbvITx1j4uLguOLuUOrLlDXb1aj8qVLmQMzT+t1hWxfM9bGc6xuhC9qS8DOjJTTyTcPWG/Dkt07ZWabnroeYVxjyHOBTteww6s9irPDrU69nihDMg1Ld5jNURMEAZfGJTJLJfs5Dw+VyPE7HPJl6dRZxYbal6lIlIDAwwegjrON817M0Gqq8NwYX6ynyQrlXuvRR5sjjeHx0XA+G9n6HRZ4jhQoalDkzOJkO3VS5oUEqah4u4HMYp4f75Hx+bJ/h03DIj69QvmQ3Uv06zYrqiCCAMlirSyWcHU3881x+fHbkTT8/suYz1qiUyUob9Bl0fme2URA5PBuNgqnCbJRv2rk/wBCmbBOvd5Eekcz3SmInJgFhuF64rZKV218l+zODBKvd4sUEqaPFrBmmSemA0N/LPkm8GLlkQXr9GnKelMjbLrDngQAi6R0hPDVR6ZO5H/fwofF8ZvS6Ps/9KWDau7Zny5QtCnoe+H1f0cjJ7X4dNwmcW7oLVPTU13VdJBxI4lV/WSPntbitlsFnJv6a1z00M0VdHBRKhK0G40XRehLeKsk7WTeZ5eaucKx2tbZey/0l8QuT/5r5NCrRLBAHHAOBa6xBWGk1ozKenWjO4lgLwTJQ5g9k6bFQsrhTT3U/gqUZy5WfkSTQTw5StkG4KlzpnDqkmvgoRnCXZaZyKnnmyibIdgUQpnPspszKyEe00h1hnR55Ikr8gOyP1O5VXF4W9d1v4J9+etNK/yaRrQ0BrbABXEkloiU3r1s6smD/9k=");}*/
         .lock_green{background-repeat:no-repeat;display:inline-block;height:64px;width:64px;background-image:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAMAAACdt4HsAAAAS1BMVEX///+m14W84aTr9uTI5rOo2Ij0+vD8/frX7cjQ6r664KHi8tex3JT6/fjM6Lq64KDw+Orn9N6s2o3D5Ky03ZjB46nd8NHZ7svF5bAJ+5OZAAACD0lEQVRYhdVXy4KDIAxcURCqWN/t/3/paguukgngcedIkgGSEJKfHxa2McvctvNiGstrsRgWWRyQy3DTvHwWAZ7lDfO6C813dHWuvX0g+6J4ZLrCzti+KOYshvpiL+WFIecW70O9NcO2pR1Meyy90/aN1x3Xv8V19KtN8gJ+t6c6Lysf1jZ1Ce0URaBYCyfQCQJ3gJb423pJ3H5w+4DMjYhOcCkokMxdoosSzBFfu/jMUYJv2kjo6toJY/bKhRBLXSgVln7Qf1UmLJ2+0j5CUEYT1iV5rDA4ggpLq/9IYNWGI6tvEigt3CMehVa3CXoXM4+pv0dgZBFAmhsEiwjNd4glTdAgwxCRqmbbtDkqNgfgh0TBVgRFPIchufdY5dmzmZp7APYIOm3pgWs7+JPHrmm6ka4/kL2lN5g/R1X0t5YokgNVU6xz0OdAXXCUxomIkBNoEDXPjQK5EC3jRYaIFkBAX+DxPZCWDf594Am7FmOlkkwC+WFYQYYigjdV++qh6oK+HuqpCIEBBKgasQSoKqk7BPA5gnrGEeBGCTiBI0AuOJqDHAKmRWByMS8Pd4AHvW3V5z1m5ghjVdGCxB0gt6yyVX3DK4fgxdvjBxEgPjLU8F8+I2zjQ3ADl0d68KppBT1hyhmaNBsLmZo2HHpubow1uVc0YHSck/PWBcM1A8fq7vS9odSV2Md/UelIX/UL02QTP+0VvS8AAAAASUVORK5CYII=");}

         @media screen {
            
            #TDM{left:5px; top:5px !important;max-height:960px;overflow:scroll;}
            #CONTENT{margin-left:10px;top:5px !important;max-height:960px;overflow:scroll;}
            .onlyprint{display:none;}
            /*page[size="A4"][layout="portrait"] {width:29.7cm;height:auto;}*/
            
         }

         @media print {

            page[size="A4"][layout="portrait"] {width:29.7cm;height:21cm;}
            
            .onlyscreen{display:none;}
            #TDM{display:none;}

            body, page{margin:0;box-shadow:0;}
            page{font-size:larger;}

            #footer.onlyprint{position:fixed;bottom:-10px;left:0;display: block;}

         }

       </style>
   
	   
   </head>
   
   <body style="overflow:hidden;">
   
      <div class="row">
         <div class="container col-md-4" id="TDM" >&nbsp;</div>	  
         <page size="A4" layout="portrait" class="container col-md-8" id="CONTENT">&nbsp;</page>
      </div>
      
      <?php 
         if (file_exists($fname='libs/jquery.min.js')) {
            echo '<script type="text/javascript" src="'.$fname.'"></script>';
         } else {
            echo '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>';
         }
         if (file_exists($fname='libs/bootstrap.min.js')) {
            echo '<script type="text/javascript" src="'.$fname.'"></script>';
         } else {
            echo '<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>';
         }
         if (file_exists($fname='libs/jquery.tablesorter.combined.min.js')) {
            echo '<script type="text/javascript" src="'.$fname.'"></script>';
         } else {
            echo '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.25.3/js/jquery.tablesorter.combined.min.js"></script>';
         }
       ?>
      
      <footer class="onlyprint" id="footer">&nbsp;</footer>	        

      <script type="text/javascript">

         $(document).ready(function() {
            
            // On page entry, get the list of .md files on the server
            ajaxify({task:'listFiles',callback:'initFiles(data)',target:'TDM'});
            
            // Size correctly depending on screen resolution
            $('#TDM').css('max-height', $(window).height()-10);
            $('#CONTENT').css('max-height', $(window).height()-10);
            $('#CONTENT').css('width', $('#CONTENT').width()-5);
            
         }); // $( document ).ready()
		 
         /**
          * Run an ajax query
          * 
          * @param {type} params
          *      task = which task should be fired
          *      param = (optional) parameter to provide for the calling task
          *      callback = (optional) Function to call once the ajax call is successfully done
          * 
          * @returns {undefined}
          */

         function ajaxify($params) { 

            var $data = new Object;
            $data.task  = (($params.task=='undefined')?'':$params.task);
            $data.param = (($params.param=='undefined')?'':$params.param);
            
            var $target='#'+(($params.target=='undefined')?'TDM':$params.target);

            $.ajax({
               beforeSend: function() {
                  $($target).html('<div><span class="ajax_loading">&nbsp;</span><span style="font-style:italic;font-size:1.5em;">Un peu de patience svp...</span></div>');
               },// beforeSend()
               async:true,
               type:'<?php echo (DEBUG===true?'GET':'POST'); ?>',
               url: '<?php echo basename(__FILE__); ?>',
               data:$data,
               datatype:'html',
               success: function (data) {     
                  
                  $($target).html(data); 
				  
                  /* jshint ignore:start */
                  var $callback=($params.callback==undefined)?'':$params.callback;
                  if($callback!=='') eval($callback);				  
                  /* jshint ignore:end */
               }
            }); // $.ajax() 
		 
         } // function ajaxify()
         
         function showFileList() {
            
            $('html, body').animate({
               'scrollTop' : $("#TDM").position().top
            });
            
            return true;
            
         } // function showFileList()
		
         /**
          * Called once 
          */
         function initFiles() {
            
            $("#tblFiles").tablesorter({
               theme: "ice",
               widthFixed: false,
               sortMultiSortKey: "shiftKey",
               sortResetKey: "ctrlKey",
               headers: {
                  0: {sorter: "text"},  // Foldername
                  1: {sorter: "text"}   // Filename
               },
               ignoreCase: true,
               headerTemplate: "{content} {icon}",
               widgets: [ 'uitheme', 'zebra', 'stickyHeaders', 'filter' ],
               initWidgets: true,
               widgetOptions: {
                  uitheme: "ice"
               },               
               sortList: [[0,0],[1,0]]
            }); // $("#tblFiles")
            
            $('#tblFiles td').click(function(e) {
               
               // By clicking on the second column, with the data-file attribute, display the file content
               if ($(this).attr('data-file')) {
                  var $fname=window.btoa(encodeURIComponent(JSON.stringify($(this).data('file'))));              
                  ajaxify({task:'display',param:$fname,callback:'afterDisplay()',target:'CONTENT'});
                  $(this).addClass("selected");                  
               }
               
               // By clicking on the first column (with foldername), get the folder name and apply a filter to only display files in that folder
               if ($(this).attr('data-folder')) {
                  var filters = $('#tblFiles').find('select.tablesorter-filter'),col=0,txt=$(this).data('folder');
                  filters.eq(col).val(txt).trigger('search', false);
               }
               
            }); // $('#tblFiles td').click()
			 
            /*$('#files li span.edit').click(function(e) {
               e.preventDefault(); e.stopImmediatePropagation();  
               var $fname=window.btoa(encodeURIComponent(JSON.stringify($(this).parent().data('file'))));
               $.ajax({
			      cache: false,
                  type:'<?php echo (DEBUG===true?'GET':'POST'); ?>',
                  url: '<?php echo basename(__FILE__); ?>',
                  data: 'task=editFile&param='+$fname,
                  dataType:'html'
               });
            });*/
			 
            return true;
			 
         } // iniFiles()
    
         /**
          * Called when a file is displayed
          */
         function afterDisplay() {
         
            $('#CONTENT').show();
            
            $('html, body').animate({
               'scrollTop' : $("#CONTENT").position().top -25
            });
            
            $('.showFileList').click(function(e) { showFileList(); })
            
            // Retrieve the heading 1 from the loaded file 
            var $title=$('#CONTENT h1').text();				  
            if ($title!=='') $('title').text($title);
            
            var $fname=$('#CONTENT h5').text();				  
            if ($fname!=='') $('#footer').html('<strong style="text-transform:uppercase;">'+$fname+'</strong>');
            
            // Force each links to be opened in a new window 
            $('a').each(function() {				
			
               var a = new RegExp('/' + window.location.host + '/');

               if (this.text!=='back') {
                  if(!a.test(this.href)) {
                     $(this).click(function(e) {
                        e.preventDefault(); e.stopPropagation();
                        window.open(this.href, '_blank');
                     });
                  }
               }
            }); // $('a').each()
			
         } // function afterDisplay()
         
      </script>
	  
   </body>
</html>   