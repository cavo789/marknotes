<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.2
* @author    : christophe@aesecure.com
* @copyright : MIT (c) 2016 - 2017
* @url       : https://github.com/cavo789/markdown#readme
* @package   : 2017-01-27T17:48:23.416Z
 */
* ?>
<?php
/* REQUIRES PHP 7.x AT LEAST */

class aeSecureFiles {
	
   /**
    * Check if a file exists and return FALSE if not.  Disable temporarily errors to avoid warnings f.i. when the file
    * isn't reachable due to open_basedir restrictions
    * 
    * @param type $filename
    * @return boolean
    */
   static public function fileExists(string $filename) : bool {
      
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
   static private function folderExists(string $folderName) : bool {
      
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
   static public function rglob(string $pattern='*', string $path='', int $flags=0, $arrSkipFolder=null) : array {
      
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
      $files=glob(rtrim($path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$pattern, $flags);

      foreach ($paths as $path) {

         if (self::folderExists($path)) { 

            // Avoid recursive loop when the folder is a symbolic link
            if (rtrim(str_replace('/',DIRECTORY_SEPARATOR,$path),DIRECTORY_SEPARATOR)==realpath($path)) {
               $arr=self::rglob($pattern, $path, $flags, $arrSkipFolder);
               if (($arr!=null) && (count($arr)>0)) $files=array_merge($files,$arr);
            } else {
               // $path is a symbolic link.  Doing a glob on a symbolic link will create a recursive
               // call and will crash the script
            }
            
         } // if(!(is_link($path))) {
         
      } // foreach
      
      // Don't use the / notation but well the correct directory separator
      foreach ($files as $key=>$value) $files[$key]=$value;
	  
      @sort($files);
	  
      return $files;
	  
   } // function rglob()
   
   /**
    * Replace file's extension
    * @param string $filename       The filename ("test.md")
    * @param string $new_extension  The new extension ("html")
    * @return string                The new filename (test.html)
    */
   static public function replace_extension(string $filename, string $new_extension) : string {
      $info = pathinfo($filename);
      return $info['dirname'].DIRECTORY_SEPARATOR.$info['filename'].'.'.$new_extension;
   } // function replace_extension
   
   /**
    * Be sure that the filename isn't something like f.i. ../../../../dangerous.file
    * Remove dangerouse characters and remove ../
    * 
    * @param string $filename
    * @return string
    * 
    * @link http://stackoverflow.com/a/2021729/1065340
    */
   static public function sanitizeFileName(string $filename) : string {
      
      // Remove anything which isn't a word, whitespace, number
      // or any of the following caracters -_~,;[]().
      // If you don't need to handle multi-byte characters
      // you can use preg_replace rather than mb_ereg_replace
      // Thanks @Łukasz Rysiak!
      
      // Remove any trailing dots, as those aren't ever valid file names.
		$filename = rtrim($filename, '.');

      // Pattern with allowed characters  PROBLEM : accentuated characters or special one (like @) should also be allowed
		//$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\\\/\_\- ]#', '#^\.#');      
      //$filename=trim(preg_replace($regex, '', $filename));
      
      // If $filename was f.i. '../../../../../'.$filename 
      // the preg_replace has change it to '/////'.$filename so remove leading /
      // Remove directory separator for Unix and Windows
      
      $filename=ltrim($filename, '\\\/');

		return $filename;
      
   } // function sanitizeFileName
   
   /**
    * Rewrite an existing file.  The function will first take a backup of the file (with new .old suffix).  If the write action is successfull, the .old file is removed
    * 
    * @param string $filename     Absolute filename
    * @param string $new_content  The new content
    * @return bool                return False in case of error
    */
   static public function rewriteFile(string $filename, string $new_content) : bool {
      
      $bReturn=FALSE;

      if (file_exists($filename)) {

         rename($filename, $filename.'.old');

         try {

            if ($handle = fopen($filename,'w')) {

               fwrite($handle, $new_content);
               fclose($handle);	

               if (filesize($filename)>0) {
                  unlink($filename.'.old');
                  $bReturn=TRUE;
               }

            }
            
         } catch (Exception $ex) {
         }
       
      } // if (file_exists($filename))
      
      return $bReturn;      
      
   } // function rewriteFile()
   
   
} // class aeSecureFiles 