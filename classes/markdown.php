<?php 

/* REQUIRES PHP 7.x AT LEAST */

// -------------------------------------------------------------------------------------------------------------------------
// 
// Can be overwritten in settings.json

// Folder in this application where .md files are stored
// Can be override : settings.json->folder (string, name of the folder)
define('DOC_FOLDER','docs');

// Does a HTML version of the visualized note should be stored on the disk ? 
// Can be override : settings.json->export->save_html (boolean, 0 or 1)
define('OUTPUT_HTML',TRUE);

// Can be override : settings.json->editor (string, full path to the editor program)
define('EDITOR','C:\Windows\System32\notepad.exe'); // default editor

// Default text, english
// Can be override : settings.json->languages->language_code (f.i. 'fr')
define('APPLY_FILTER','Searching for %s');
define('CONFIDENTIAL','confidential');
define('EDIT_FILE','Edit');
define('FILES_FOUND','%s has been retrieved');
define('IS_ENCRYPTED','This information is encrypted in the original file and decoded here for screen display');
define('OPEN_HTML','Open in a new window');
define('PLEASE_WAIT','Please wait...');
define('SEARCH_PLACEHOLDER','Type here keywords and search for them');
define('SEARCH_NO_RESULT','Sorry, the search is not successfull');

// When images are too big, force a resize by css to a max-width of ...
// Can be override : settings.json->page->img_maxwidth (integer)
define('IMG_MAX_WIDTH','800');

//
// -------------------------------------------------------------------------------------------------------------------------

// Max allowed size for the search string
define ('SEARCH_MAX_LENGTH',100);

defined('DS') or define('DS',DIRECTORY_SEPARATOR);

// Requires PHP 7.x
         
class aeSecureMarkdown {
   
   private $_json=null;                 // JSON, contains the content of the settings.json file

   private $_rootFolder='';             // root folder of the web application (f.i. "C:\Christophe\Documents\")
   
   private $_settingsTemplateScreen=''; // template to use; default is /templates/screen.php
   private $_settingsTemplateHTML='';   // template to use; default is /templates/html.php
   private $_settingsEditor='';         // defaut editor program 
   
   private $_settingsFontName='';       // Google fontname if specified in the settings.json file
   
   private $_settingsDocsFolder='';     // subfolder f.i. 'docs' where markdown files are stored (f.i. "Docs\")
   private $_settingsLanguage='en';     // user's language
   
   private $_encryptionMethod='';       // Method to use for the encryption
   private $_encryptionPassword='';     // Password for the encryption / decryption
   
   private $_saveHTML=TRUE;             // When displaying a .md file, generate and store its .html rendering
      
   /**
    * Class constructor : initialize a few private variables
    * 
    * @param string $folder    Root folder of the website (f.i. "C:\Christophe\Documents\").
    * @return boolean
    */
   function __construct(string $folder) {
      
      if(!class_exists('aeSecureEncrypt')) require_once 'encrypt.php';
      if(!class_exists('aeSecureFiles'))   require_once 'files.php';
      if(!class_exists('aeSecureFct'))     require_once 'functions.php';
      
      // Get the root folder and be sure the folder ends with a slash
      $this->_rootFolder=rtrim($folder,'/').'/';

      // Initialize with default values
      $this->_settingsDocsFolder='';
      $this->_settingsTemplateScreen='screen';
      $this->_settingsTemplateHTML='html';
      $this->_settingsLanguage='en';
      $this->_saveHTML=OUTPUT_HTML;
      $this->_settingsImgMaxWidth=IMG_MAX_WIDTH;
      
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

         if(isset($this->_json['editor'])) {
            $this->_settingsEditor=rtrim($this->_json['editor']);
            if(!file_exists($this->_settingsEditor)) $this->_settingsEditor=EDITOR;
         } else {
            $this->_settingsEditor=EDITOR;
         }
         
         // Retrieve the subfolder if any
         if(isset($this->_json['folder']))   $this->_settingsDocsFolder=rtrim($this->_json['folder'],'/').'/';  // Be sure that there is a slash at the end
         if(isset($this->_json['language'])) $this->_settingsLanguage=$this->_json['language'];
         
         if(isset($this->_json['templates'])) {
            
            $tmp=$this->_json['templates'];
            
            // Process all templates (screen and html)
            for($i=0;$i<2;$i++) {               
               
               $name= ($i==0 ? 'screen' : 'html');

               if (trim($tmp[$name])!='') { // can't be empty

                  if (preg_match('/^[A-Za-z0-9-_\.]+$/',trim($tmp[$name]))) { // should only contains letters, figures or dot/minus/underscore

                     if (is_file(dirname(__DIR__).'/templates/'.trim($tmp[$name]).'.php')) {                     
                        
                        // The template exists, ok.
                        if($i==0) {
                           $this->_settingsTemplateScreen=trim($tmp[$name]);
                        } else {
                           $this->_settingsTemplateHTML=trim($tmp[$name]);
                        }
                        
                     } else {
                        // The specified template doesn't exists. Back to the default one;
                        if (DEBUG) echo '<span style="font-size:0.8em;">'.__FILE__.'::'.__LINE__.'</span>&nbsp;-&nbsp;';
                        echo '<strong><em>Template ['.$tmp[$name].'] not found, please review your settings.json file.</em></strong>';
                        die();
                     }
                  }
               }
            } // for()
            
         } // if(isset($this->_json['templates'])) {
         
         // Retrieve the password if mentionned
         if(isset($this->_json['password'])) $this->_settingsPassword=$this->_json['password'];
         
          // Get page settings
         if(isset($this->_json['page'])) {
            $tmp=$this->_json['page'];
            // Spaces should be replaced by a "+" sign
            if(isset($tmp['google_font'])) $this->_settingsFontName=str_replace(' ','+',$tmp['google_font']);
            if(isset($tmp['img_maxwidth'])) $this->_settingsImgMaxWidth=str_replace(' ','+',$tmp['img_maxwidth']);            
         }
        
         // Get encryption settings
         if(isset($this->_json['encryption'])) {
            $tmp=$this->_json['encryption'];
            if(isset($tmp['password'])) $this->_encryptionPassword=$tmp['password'];
            if(isset($tmp['method']))   $this->_encryptionMethod=$tmp['method'];
         }
         
         // Get export settings
         if(isset($this->_json['export'])) {
            $tmp=$this->_json['export'];
            if(isset($tmp['save_html'])) $this->_saveHTML=((trim($tmp['save_html'])=='1')?true:false);
         }
         
      }  
      
      return TRUE;
      
   } // function ReadSettings()

   /**
    * Return the value of a setting
    * 
    * @param string $name        f.i. ImgMaxWidth
    * @param type $defaultValue  
    * @return type
    */      
   private function getSetting(string $name, $defaultValue) {
      switch ($name) {
         case 'ImgMaxWidth' :
            return $this->_settingsImgMaxWidth;
            break;
         default:
           return null;        
      }
   } // function getSetting()
   
   /**
    * Get the list of .md files.  This list will be used in the "table of contents"
    * 
    * @return string
    */   
   private function ListFiles() : array {

      $arrFiles=array_unique(aeSecureFiles::rglob('*.md',$this->_rootFolder.$this->_settingsDocsFolder));
    
      // Be carefull, folders / filenames perhaps contains accentuated characters
      $arrFiles=array_map('utf8_encode', $arrFiles);
      
      // Sort, case insensitve
      natcasesort($arrFiles);   
     
      $root=$this->_rootFolder.$this->_settingsDocsFolder;
      
      $return['settings']['root']=$root;
      
      // get the list of folders and generate a "tags" node
      
      $dirs = array_filter(glob($root.'*'), 'is_dir');
      natcasesort($dirs);

      $iter = new RecursiveIteratorIterator(
         new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
         RecursiveIteratorIterator::SELF_FIRST,
         RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
      );

      $paths = array();
      foreach ($iter as $path => $dir) {
         if ($dir->isDir()) $paths[] = basename($path);
      }
      
      $paths=aeSecureFct::array_iunique($paths,SORT_STRING);
      
      natcasesort($paths);
    
      $tmp='';
      foreach ($paths as $dir) $tmp.=utf8_encode(basename($dir)).';';      
      $tmp=rtrim($tmp,';');
      
      $return['tags']= explode(';',$tmp); 

      // Get the number of files
      $return['count']=count($arrFiles);
          
      // And process every files
      foreach ($arrFiles as $file) {
         
         // Don't mention the full path, should be relative for security reason
         $file=str_replace($this->_rootFolder.$this->_settingsDocsFolder,'',$file);
         
         $folder=in_array(trim(dirname($file)), array(DIRECTORY_SEPARATOR,'.'))?'(root)':dirname($file);
         
         $tmp=array();
         $tmp['folder']=$folder;
         $tmp['file']=$file;
         $tmp['display']=str_replace('.md','',basename($file));
         $return['results'][]=$tmp;
         
      }
      
      return $return;
	  
   } // function ListFiles()	  
  
   /**
    * Return the HTML rendering of a .md file
    * 
    * @param type $filename   Relative filename of the .md file to open and display
    * @return string          HTML rendering 
    */ 
   private function ShowFile(string $filename) : string {
	    
      $fullname=str_replace('/', DIRECTORY_SEPARATOR,utf8_decode($this->_rootFolder.$this->_settingsDocsFolder.$filename));
      
      $markdown=file_get_contents($fullname);
      
      $icons='';

      // Check if there are <encrypt> tags.  If yes, check the status (encrypted or not) and retrieve its content
      $matches = array();    
      // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
      preg_match_all('/<encrypt[[:blank:]]*([^>]*)>([\\S\\n\\r\\s]*?)<\/encrypt>/', $markdown, $matches);
      
      // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
      if (count($matches[1])>0) {
         
         $icon_stars='<i class="icon_encrypted fa fa-lock onlyscreen" aria-hidden="true" '.
            'data-encrypt="true" title="'.str_replace('"','\"',$this->getText('is_encrypted','IS_ENCRYPTED')).'"></i>';
         
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
         // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
         preg_match_all('/<encrypt[[:blank:]]*[^>]*>([\\S\\n\\r\\s]*?)<\/encrypt>/', $markdown, $matches);

         // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
         if (count($matches[1])>0) {
            
            $j=count($matches[0]);

            $i=0;
         
            for($i;$i<$j;$i++) {

               // Great, the info is already encrypted
               
               //$icons='<i id="icon_lock" class="fa fa-lock" aria-hidden="true"></i>';
               
               $decrypt=$aesEncrypt->sslDecrypt($matches[1][$i],NULL);

               $markdown=str_replace($matches[1][$i], $icon_stars.$decrypt.$icon_stars, $markdown);
               
            }

         }
         
         // Release 
         
         unset($aesEncrypt);
         
      } // if (count($matches[1])>0)
      
      // -----------------------------------
      // Add additionnal icons at the left

      $fnameHTML=aeSecureFiles::replace_extension($fullname,'html');

      // Generate the URL (full) to the html file, f.i. http://localhost/docs/folder/file.html
      $tmp = str_replace('\\','/',rtrim(aeSecureFct::getCurrentURL(FALSE,TRUE),'/').str_replace(dirname($_SERVER['SCRIPT_FILENAME']),'',$fnameHTML));
   
      // Open new window icon
      if($this->_saveHTML===TRUE) $icons.='<i data-file="'.utf8_encode(str_replace(DS,'/',$tmp)).'" id="icon_window" class="fa fa-external-link" aria-hidden="true" title="'.$this->getText('open_html','OPEN_HTML').'"></i>';
      
      // Edit icon : only if an editor has been defined
      if ($this->_settingsEditor!=='') {
         $icons.='<i id="icon_edit" class="fa fa-pencil-square-o" aria-hidden="true" title="'.$this->getText('edit_file','EDIT_FILE').'" data-file="'.utf8_encode($filename).'"></i>';
      }

      require_once("libs/Parsedown.php");
      $Parsedown=new Parsedown();      
      $html=$Parsedown->text($markdown);
      
      // Check if the .html version of the markdown file already exists; if not, create it 
      if ($this->_saveHTML===TRUE) {

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
         
               // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
               preg_match_all('/<encrypt[[:blank:]]*[^>]*>([\\S\\n\\r\\s]*?)<\/encrypt>/', $tmp, $matches);
               //preg_match_all('/<encrypt[[:blank:]]*[^>]*>(.*?)<\/encrypt>/', $tmp, $matches);

               // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
               if (count($matches[0])>0) {

                  $j=count($matches[0]);

                  $i=0;

                  for($i;$i<$j;$i++) {
                     $tmp=str_replace($matches[0][$i], '<strong class="confidential">'.$this->getText('confidential','CONFIDENTIAL').'</strong>', $tmp);
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

                  if (is_file($template=dirname(__DIR__).'/templates/'.$this->_settingsTemplateHTML.'.php')) {         
                     
                     $content=file_get_contents($template);
         
                     // Write the file but first replace variables
                     $content=str_replace('%TITLE%',$title,$content);
                     $content=str_replace('%CONTENT%',$tmp,$content);
                  
                     // Perhaps a Google font should be used.  
                     $sFont=self::GoogleFont();
                     $content=str_replace('%FONT%',$sFont,$content);
                  
                     fwrite($handle,$content);

                     fclose($handle);		
                  }

               } // if ($handle = fopen($fname,'w+'))

             } // if (!file_exists($fname))

		 } // if (is_writable(dirname($fname)))

      } // if (OUTPUT_HTML===TRUE)
      
      // Generate the URL (full) to the html file, f.i. http://localhost/docs/folder/file.html
      $fnameHTML = str_replace('\\','/',rtrim(aeSecureFct::getCurrentURL(FALSE,TRUE),'/').str_replace(str_replace('/',DS,dirname($_SERVER['SCRIPT_FILENAME'])),'',$fnameHTML));
      
      $html=str_replace('</h1>', '</h1><div id="icons" class="onlyscreen fa-3x"><i id="icon_printer" class="fa fa-print" aria-hidden="true"></i>'.$icons.'</div>',$html);
      $html=str_replace('src="images/', 'src="'.DOC_FOLDER.'/'.str_replace(DS,'/',dirname($filename)).'/images/',$html);
      $html=str_replace('href="files/', 'href="'.DOC_FOLDER.'/'.str_replace(DS,'/',dirname($filename)).'/files/',$html);
      $html='<div class="onlyscreen filename">'.utf8_encode($fullname).'</div>'.$html.'<hr/>';

      return $html;
	  
   } // function ShowFile()
   
   /**
    * Return the translation of a given text
    * @param string $variable
    */
   private function getText(string $variable, string $default) : string {
      
      if (isset($this->_json['languages'][$this->_settingsLanguage])) {
         $lang=&$this->_json['languages'][$this->_settingsLanguage];
         return isset($lang[$variable]) ? $lang[$variable] : (trim($default)!=='' ? constant($default) : '');
      } else {
         return (trim($default)!=='' ? constant($default) : '');
      }
      
   } // function getText()
   
   /**
    * Search for "$keywords" in the filename or in the file content.  Stop on the first occurence to speed up
    * the process.
    * 
    * Note : if the content contains encrypted data, the data is decrypted so the search can be done on these info too
    * 
    * @param string $keywords
    * @return array
    */
   private function Search(string $keywords) : array {

      $arrFiles=array_unique(aeSecureFiles::rglob('*.md',$this->_rootFolder.$this->_settingsDocsFolder));
      
      if (count($arrFiles)==0) return null;

      // Be carefull, folders / filenames perhaps contains accentuated characters
      $arrFiles=array_map('utf8_encode', $arrFiles);
      
      // Sort, case insensitve
      natcasesort($arrFiles);   
      
      $return=array();

      // Initialize the encryption class
      $aesEncrypt=new aeSecureEncrypt($this->_encryptionPassword, $this->_encryptionMethod);

      //$return['keywords']=$keywords;
      foreach ($arrFiles as $file) {
         
         // Don't mention the full path, should be relative for security reason
         $file=str_replace($this->_rootFolder.$this->_settingsDocsFolder,'',$file);
         
         // If the keyword can be found in the document title, yeah, it's the fatest solution,
         // return that filename
         
         if (stripos($file, $keywords)!==FALSE) {
         
            // Found in the filename => stop process of this file
            $return['files'][]=$file;
            
         } else {
            
            // Open the file and check against its content
            
            $fullname=utf8_decode($this->_rootFolder.$this->_settingsDocsFolder.$file);
            $content=file_get_contents($fullname);
            
            if (stripos($content, $keywords)!==FALSE) {
               
               // The searched pattern has been found in the filecontent (unencrypted), great, return the filename
               
               $return['files'][]=$file;
               
            } else {
               
               // Not found in filename and filecontent (unencrypted); check if there are encrypted info

               // Check if the note has encrypted data.  If you, decrypt and search in the decrypted version
               
               $matches = array();         
               // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
               preg_match_all('/<encrypt[[:blank:]]*([^>]*)>([\\S\\n\\r\\s]*?)<\/encrypt>/', $content, $matches);

               // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
               if (count($matches[1])>0) {
                  
                  $j=count($matches[0]);

                  $i=0;

                  // Loop and process every <encrypt> tags
                  // For instance : <encrypt data-encrypt="true">ENCRYPTED TEXT</encrypt>

                  for($i;$i<$j;$i++) {
                        
                     // Retrieve the attributes (f.i. data-encrypt="true")            
                     $attributes=$matches[1][$i];

                     // Are there data-encrypt=true content ? If yes, unencrypt it
                     $tmp=array();
                     preg_match('#data-encrypt="(.*)"#', $attributes, $tmp);
                     
                     if(count($tmp)>0) {
                        // Only when data-encrypt="true" is found, consider the content has an encrypted one.
                        $isEncrypted=(strcasecmp(rtrim($tmp[1]),'true')===0?TRUE:FALSE);
                        $decrypt=$aesEncrypt->sslDecrypt($matches[2][$i],NULL);
                        $content=str_replace($matches[2][$i], $decrypt, $content);
                     }                  

                     // Now $content is full decrypted, search again
                     if (stripos($content, $keywords)!==FALSE) { 
                        $return['files'][]=$file;
                        break;                        
                     }
                  
                  } // for($i;$i<$j;$i++)
                  
               } // if (count($matches[1])>0) {
                  
            } // if (stripos($content, $keywords)!==FALSE) {
            
         } // if (stripos($file, $keywords)!==FALSE)
            
      } // foreach ($arrFiles as $file)
      
      unset($aesEncrypt);
      
      return $return;
      
   } // function Search()
   
   /**
    * Start the editor program
    * 
    * @param string $filename   Relative filename like "privÃ©\Fisc.md".  Need to be utf8_decoded
    * @return bool
    */
   private function Edit(string $filename) : bool {
      
      if ($filename!="") {
         
         // Get the fullname like "C:\Christophe\notes\docs\privé\Fisc.md"
         $fullname=utf8_decode($this->_rootFolder.$this->_settingsDocsFolder.$filename);

         try {

            // Make the command line : the editor followed by the name of the file that should be edited
            // For instance : C:\Windows\System32\notepad.exe "C:\Christophe\notes\docs\privé\Fisc.md"
            $cmd = $this->_settingsEditor.' "'.$fullname.'"'; // Open the directory and select the file

            //echo __LINE__.'  **'.$cmd.'**<br/>';   
            if (substr(php_uname(), 0, 7) == "Windows") {
               pclose(popen("start /B ".$cmd, "r"));
            } else {
               exec($cmd);
            }

         } catch (Exception $ex) {
            die('Error in line '.__LINE__.' : '.$ex->getMessage());
         }
         
      } // if ($filename!="") 
    
      return true;
      
   } // function Edit()
   
   /**
    * Detect if a Google Font was specified in the json and if so, generate a string to load that font
    * 
    * @return string
    */
   private function GoogleFont() : string {
      
      $result='';
      
      if ($this->_settingsFontName!=='') {
         
         $result='<link href="https://fonts.googleapis.com/css?family='.$this->_settingsFontName.'" rel="stylesheet">';

         $i=0;
         $result.='<style>';
         $sFontName=str_replace('+',' ',$this->_settingsFontName);
         for($i=1;$i<7;$i++) $result.='page h'.$i.'{font-family:"'.$sFontName.'";}';
         $result.='</style>';
         
      } // if ($this->_settingsFontName!=='')
     
      return $result;
      
   } // function GoogleFont()
   
   /**
    * Get the main interface of the application
    * 
    * @return string  html content
    */
   private function showInterface() : string {
      
      if (is_file($template=dirname(__DIR__).'/templates/'.$this->_settingsTemplateScreen.'.php')) {
         
         $html= file_get_contents($template);
         
         // replace variables         
         $html=str_replace('%APP_NAME%', APP_NAME, $html);
         $html=str_replace('%APP_NAME_64%', base64_encode(APP_NAME), $html);
         $html=str_replace('%IMG_MAXWIDTH%', self::getSetting('ImgMaxWidth','800'), $html);
         
         $html=str_replace('%EDT_SEARCH_PLACEHOLDER%', self::getText('search_placeholder','SEARCH_PLACEHOLDER'), $html);
         $html=str_replace('%EDT_SEARCH_MAXLENGTH%', SEARCH_MAX_LENGTH, $html);
         
         
         // Define the global markdown variable.  Used by the assets/js/markdown.js script
         $JS=
            "\nvar markdown = {};\n".
            "markdown.message={};\n".
            "markdown.message.apply_filter='".str_replace("'","\'",self::getText('apply_filter','APPLY_FILTER'))."';\n".
            "markdown.message.filesfound='".str_replace("'","\'",self::getText('files_found','FILES_FOUND'))."';\n".
            "markdown.message.pleasewait='".str_replace("'","\'",self::getText('please_wait','PLEASE_WAIT'))."';\n".
            "markdown.message.search_no_result='".str_replace("'","\'",self::getText('search_no_result','SEARCH_NO_RESULT'))."';\n".
            "markdown.url='index.php';\n".
            "markdown.settings={};\n".
            "markdown.settings.debug=".DEBUG.";\n".
            "markdown.settings.search_max_width=".SEARCH_MAX_LENGTH.";";
         
         $html=str_replace('%MARKDOWN_GLOBAL_VARIABLES%', $JS, $html);
         
         
         // if any, output the code for the Google Font (see settings.json)
         $html=str_replace('<!--%FONT%-->', self::GoogleFont(), $html);

         // if present, add your custom stylesheet if the custom.css file is present. That file should be present in the root folder; not in /assets/js
         $html=str_replace('<!--%CUSTOM_CSS%-->', aeSecureFct::addStylesheet('custom.css'), $html);
         
         // if present, add your custom javascript if the custom.js file is present. That file should be present in the root folder; not in /assets/js
         $html=str_replace('<!--%CUSTOM_JS%-->', aeSecureFct::addJavascript('custom.js'), $html);
         
         return $html;
         
      } else { // if (is_file($template=dirname(__DIR__).'/templates/main.php'))
         
         return 'ERROR - The file '.$template.' is missing';
         
      } // if (is_file($template=dirname(__DIR__).'/templates/main.php'))
      
   } // function showInterface()   
   
   /**
    * Entry point of this class, run a task
    * 
    * @param string $task
    */
   public function process(string $task) {
      
      switch ($task) {

         case 'display':

            header('Content-Type: text/html; charset=utf-8'); 
            $fname=json_decode(urldecode(base64_decode(aeSecureFct::getParam('param','string','',false))));
            $result=self::ShowFile($fname);
            echo $result;
            die();

         case 'edit' : 

            $fname=json_decode(urldecode(base64_decode(aeSecureFct::getParam('param','string','',false))));
            self::Edit($fname);            
            die();

         case 'listFiles':

            // Retrieve the list of .md files.   
            // Default task
            
            header('Content-Type: application/json'); 
            echo json_encode(self::ListFiles(), JSON_PRETTY_PRINT);
            die();

         case 'search': 

            header('Content-Type: application/json'); 
            $json=self::Search(urldecode(aeSecureFct::getParam('param','string','',true, SEARCH_MAX_LENGTH)));
            echo json_encode($json, JSON_PRETTY_PRINT); 
            die();
            
         default :  // task=main => display the main interface
            
            echo self::showInterface();
            die();

      } // switch ($task)
   
   } // function process()
   
} // class aeSecureMarkdown

?>