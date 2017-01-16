<?php 
declare(strict_types=1);

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
define('ALLOW_POPUP_PLEASE','Please allow popup for this site');
define('APPLY_FILTER','Searching for %s');
define('APPLY_FILTER_TAG','Display notes containing this tag');
define('COPY_LINK','Copy the link to this note in the clipboard');
define('COPY_LINK_DONE','The URL of this note has been copied into the clipboard');
define('CONFIDENTIAL','confidential');
define('DISPLAY_THAT_NOTE','Display that note');
define('EDIT_FILE','Edit');
define('ERROR','Error');
define('FILE_NOT_FOUND','The file [%s] doesn\'t exists (anymore)');
define('FILES_FOUND','%s has been retrieved');
define('IS_ENCRYPTED','This information is encrypted in the original file and decoded here for screen display');
define('OPEN_HTML','Open in a new window');
define('PLEASE_WAIT','Please wait...');
define('PRINT_PREVIEW','Print preview');
define('SEARCH_PLACEHOLDER','Type here keywords and search for them');
define('SEARCH_NO_RESULT','Sorry, the search is not successfull');
define('SLIDESHOW','slideshow');

// When images are too big, force a resize by css to a max-width of ...
// Can be override : settings.json->page->img_maxwidth (integer)
define('IMG_MAX_WIDTH','800');

// Prefix to use to indicate a word as a tag
define('PREFIX_TAG','§');

//
// -------------------------------------------------------------------------------------------------------------------------

// Max allowed size for the search string
define ('SEARCH_MAX_LENGTH',100);

defined('DS') or define('DS',DIRECTORY_SEPARATOR);

// Requires PHP 7.x
         
class aeSecureMarkdown {
   
   private $_json=null;                 // JSON, contains the content of the settings.json file

   private $_rootFolder='';             // root folder of the web application (f.i. "C:\Christophe\Documents\")
   
   private $_DEBUGMODE=FALSE;           // Debug mode enabled or not
   private $_DEVMODE=FALSE;             // Development mode enabled or not
   
   private $_settingsTemplateScreen=''; // template to use; default is /templates/screen.php
   private $_settingsTemplateHTML='';   // template to use; default is /templates/html.php
   
   private $_useCache=TRUE;             // Use browser's cache
   
   private $_arrTagsAutoSelect=array(); // Tags to automatically select when displaying the page
   
   private $_settingsEditAllowed=TRUE;  // Allow editions
   private $_settingsFontName='';       // Google fontname if specified in the settings.json file
   
   private $_settingsDocsFolder='';     // subfolder f.i. 'docs' where markdown files are stored (f.i. "Docs\")
   private $_settingsLanguage='en';     // user's language
   
   private $_encryptionMethod='aes-256-ctr';   // Method to use for the encryption, default method
   private $_encryptionPassword='';     // Password for the encryption / decryption
   
   private $_OptimizeLazyLoad=0;        // Optimization : lazyload enabled or not ? 
   
   private $_saveHTML=TRUE;             // When displaying a .md file, generate and store its .html rendering
   
   private $aeDebug=null; 
   private $aeJSON=null;
      
   /**
    * Class constructor : initialize a few private variables
    * 
    * @param string $folder    Root folder of the website (f.i. "C:\Christophe\Documents\").
    * @return boolean
    */
   function __construct(string $folder) {
      
      if(!class_exists('aeSecureDebug')) { 
         require_once 'debug.php'; 
         $this->aeDebug=aeSecureDebug::getInstance(); 
      }
      
      if(!class_exists('aeSecureEncrypt')) require_once 'encrypt.php';
      if(!class_exists('aeSecureFiles'))   require_once 'files.php';
      if(!class_exists('aeSecureFct'))     require_once 'functions.php';
      if(!class_exists('aeSecureJSON')) {
         require_once 'json.php';
         $this->aeJSON=aeSecureJSON::getInstance();
      }
      
      // Get the root folder and be sure the folder ends with a slash
      $this->_rootFolder=rtrim($folder,'/').'/';

      // Initialize with default values
      $this->_settingsDocsFolder='';
      $this->_settingsTemplateScreen='screen';
      $this->_settingsTemplateHTML='html';
      $this->_useCache=TRUE;
      $this->_settingsLanguage='en';
      $this->_saveHTML=OUTPUT_HTML;
      $this->_settingsImgMaxWidth=IMG_MAX_WIDTH;
      $this->_OptimizeLazyLoad=0;
      
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
   
         $this->_json=$this->aeJSON->json_decode($fname,true);

         if(isset($this->_json['tags'])) {
            $this->_arrTagsAutoSelect=$this->_json['tags'];
         }
         
         if(isset($this->_json['debug'])) {
            $this->_DEBUGMODE=($this->_json['debug']==1?TRUE:FALSE); // Debug mode enabled or not
         }
         
         if ($this->_DEBUGMODE===TRUE) {
            $this->aeDebug->enable();
            $this->aeJSON->debug(TRUE);
         } else {	   
            error_reporting(E_ALL & ~ E_NOTICE);	  
         }
         
         if(isset($this->_json['development'])) $this->_DEVMODE=($this->_json['development']==1?TRUE:FALSE); // Development mode enabled or not
   
         if(isset($this->_json['editor']))      $this->_settingsEditAllowed=($this->_json['editor']==1?TRUE:FALSE); // Allow editions
         
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
                        if ($this->_DEBUGMODE) echo '<span style="font-size:0.8em;">'.__FILE__.'::'.__LINE__.'</span>&nbsp;-&nbsp;';
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
         
         // Get optimisation settings
         if(isset($this->_json['optimisation'])) {
            $tmp=$this->_json['optimisation'];
            if(isset($tmp['cache'])) $this->_useCache=(($tmp['cache']==1)?true:false);
            if(isset($tmp['lazyload'])) $this->_OptimizeLazyLoad=(($tmp['lazyload']==1)?1:0);
         }
         
         // Get export settings
         if(isset($this->_json['export'])) {
            $tmp=$this->_json['export'];
            if(isset($tmp['save_html'])) $this->_saveHTML=(($tmp['save_html']==1)?true:false);
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
    * Retrieve the list of folder names and tags.  Used for the search entry, allowing auto-completion
    * 
    * @return array
    */
   private function getTags() : array {
    
      $root=$this->_rootFolder.$this->_settingsDocsFolder;
      
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
      
      $return=array();
      
      if (aeSecureFiles::fileExists($fname=$this->_rootFolder.'tags.json')) {
         if(filesize($fname)>0) {
            $arrTags=$this->aeJSON->json_decode($fname,true);      
            foreach($arrTags as $tag) {
               $return[]=array('name'=>$tag,'type'=>'tag');
            }
         }
      }
       
      $tmp=explode(';',$tmp); 
      foreach ($tmp as $folder) {
         $return[]=array('name'=>$folder,'type'=>'folder');
      }
      
      return $return;
  
   } // function getTags()
   
   /**
    * Store tags in the tags.json file 
    * 
    * @param array $arrTags  Array with tags like array('§tag1','§tag2', ...)
    * @return bool
    */
   private function StoreTags(array $arrTags) : bool {
    
      if (count($arrTags)>0) {
         
         $arrJSON=array();
         if (aeSecureFiles::fileExists($fname=$this->_rootFolder.'tags.json')) {
            if(filesize($fname)>0) $arrJSON=$this->aeJSON->json_decode($fname,true);
         }
    
         $arrTags=aeSecureFct::array_iunique($arrTags);
         natcasesort ($arrTags);     
         
         if (count($arrJSON)==0) {
            
            // First time, the tags.json wasn't yet created
            
            $bAdd=TRUE;
            
            foreach($arrTags as $tag) $arrJSON[]=$tag;
            
         } else { // if (count($arrJSON)==0)
         
            $bAdd=FALSE;
            foreach($arrTags as $tag) {

               // Use preg_grep and not in_array to be able to make a case insensitive search
               if (count(preg_grep("/".$tag."/i" , $arrJSON))==0) {
                  $bAdd = TRUE; 
                  $arrJSON[]=$tag;            
               }
               
            } // foreach()
            
         } // if (count($arrJSON)==0)
         
         try {
            $sJSON=json_encode($arrJSON, JSON_PRETTY_PRINT);
         } catch (Exception $ex) {
            $sJSON='';
         }

         if (($bAdd===TRUE)&&($sJSON!='')) {   

            if ($handle = fopen($fname,'w')) {
               sort($arrJSON);
               fwrite($handle, $sJSON);
               fclose($handle);		
            }    
         }
      
      }
      return true;
      
   } // function StoreTags()
   
   /**
    * Get the list of .md files.  This list will be used in the "table of contents"
    * 
    * @return string
    */   
   private function ListFiles() : array {
      
      $i=0;

      $arrFiles=aeSecureFct::array_iunique(aeSecureFiles::rglob('*.md',$this->_rootFolder.$this->_settingsDocsFolder));
    
      // Be carefull, folders / filenames perhaps contains accentuated characters
      $arrFiles=array_map('utf8_encode', $arrFiles);
      
      // Sort, case insensitve
      natcasesort($arrFiles);   
     
      $root=$this->_rootFolder.$this->_settingsDocsFolder;
      
      $return['settings']['root']=$root;
      
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
         
      } // foreach()      
      
      // --------------------------------------------------------------------------------------
      // Populate the tree that will be used for jsTree (see https://www.jstree.com/docs/json/)
      
      foreach ($arrFiles as $file) {
         // Don't mention the full path, should be relative for security reason
         $file=str_replace($this->_rootFolder.$this->_settingsDocsFolder,'',$file);
         
         $folder=in_array(trim(dirname($file)), array(DIRECTORY_SEPARATOR,'.'))?'(root)':dirname($file);
         
         $i+=1;
         
         $tmp=array();
         $tmp['id']=$i;
         $tmp['text']=$file;
         $return['tree'][]=$tmp;

      } // foreach()
      
      // --------------------------------------------------------------------------------------

      return $return;
	  
   } // function ListFiles()	  
  
   /**
    * Display an error
    * 
    * @param string $msg   The error message to display
    * @param bool $die     Die() once displayed or not ?
    * @return bool         TRUE
    */
   private function ShowError(string $msg, bool $die = TRUE) : bool {
      
      if ($this->_DEBUGMODE) $msg .= ' <em class="text-info">(called by '.debug_backtrace()[1]['function'].', line '.debug_backtrace()[1]['line'].')</em>';
      
      $msg='<div class="error bg-danger">'.$this->getText('error','ERROR').' - '.$msg.'</div>';
      if ($die===TRUE) {         
         die($msg);
      } else {
         echo $msg;
         return true;
      }
      
   } // function ShowError()
   
   /**
    * Return the HTML rendering of a .md file
    * 
    * @param type $filename   Relative filename of the .md file to open and display
    * @return string          HTML rendering 
    */ 
   private function ShowFile(string $filename) : string {
      
      $fullname=str_replace('/', DIRECTORY_SEPARATOR,utf8_decode($this->_rootFolder.$this->_settingsDocsFolder.ltrim($filename,DS)));

      if (!file_exists($fullname)) {
         self::ShowError(str_replace('%s','<strong>'.$fullname.'</strong>',$this->getText('file_not_found','FILE_NOT_FOUND')),TRUE);
      }

      $markdown=file_get_contents($fullname);
      
      $old=$markdown;
      
      // -----------------------------------------------------------------------
      // URL Cleaner : Make a few cleaning like replacing space char in URL or in image source
      // Replace " " by "%20"
      
      if (preg_match_all('/<img *src *= *[\'|"]([^\'|"]*)/', $markdown, $matches)) {
         foreach($matches[1] as $match) {
            $sMatch=str_replace(' ','%20',$match);
            $markdown=str_replace($match,$sMatch,$markdown);
         }
      }
      
      // And do the same for links
      if (preg_match_all('/<a *href *= *[\'|"]([^\'|"]*)/', $markdown, $matches)) {
         foreach($matches[1] as $match) {
            $sMatch=str_replace(' ','%20',$match);
            $markdown=str_replace($match,$sMatch,$markdown);
         }
      }      

      // -----------------------------------------------------------------------
      // Check if the file contains words present in the tags.json file : if the file being displayed contains a word (f.i. "javascript") that is in the 
      // tags.json (so it's a known tag) and that word is not prefixed by the "§" sign add it : transform the "plain text" word and add the "tag" prefix 

      if (aeSecureFiles::fileExists($fname=$this->_rootFolder.'tags.json')) {
         if(filesize($fname)>0) {
            
            $arrTags=$this->aeJSON->json_decode($fname);
            foreach($arrTags as $tag) {

               // For each tag, try to find the word in the markdown file 
               
               // /( |\\n|\\r|\\t)+                Before the tag, allowed : space, carriage return, linefeed or tab  
               // [^`\/\\#_\-§]?                   Before the tag, not allowed : `, /, \, #, -, _ and § (the PREFIX_TAG)
               // ('.preg_quote($tag).')           The tag term (f.i. "javascript"
               // (\\n|,|;|\\.|\\)|[[:blank:]]|$)  After the tag, allowed : carriage return, comma, dot comma, dot, ending ), tag or space or end of line
               
               // Capture the full line (.* ---Full Regex--- .*)
               preg_match_all('/(.*( |\\n|\\r|\\t)+('.preg_quote($tag).')(\\n|,|;|\\.|\\)|\\t| |$).*)/i', $markdown, $matches);

               foreach ($matches[0] as $match) {
                  
                  if (count($match)>0) {

                     preg_match('/(.*( |\\n|\\r|\\t)+('.preg_quote($tag).')(\\n|,|;|\\.|\\)|\\t| |$).*)/i', $match, $matches);

                     // Replace, in the line, the word f.i.    (don't use a preg_replace because preg_replace will replace all occurences of the word)

                     //   Line  : Start a SSH connexion     (original)
                     //   By    : Start a §SSH connexion    (new line)

                     // $matches[2] : what was just before the tag      f.i.   " Start a SSH, then ..."  => the space before SSH
                     // $matches[3] : the tag                                  " Start a SSH, then ..."  => SSH
                     // $matches[4] : what was just after the tag              " Start a SSH, then ..."  => the comma after SSH

                     $sLine=str_ireplace($matches[2].$matches[3].$matches[4],$matches[2].PREFIX_TAG.$matches[3].$matches[4],$matches[0]);

                     // And now, replace the original line ($matches[0]) by the new one in the document.

                     $markdown=str_replace($matches[0],$sLine,$markdown);
                     
                  } // if (count($match)>0)

               } // foreach ($matches[0] as $match)

            } // foreach
            
         } // if(filesize($fname)>0)
         
      } // if (aeSecureFiles::fileExists($fname=$this->_rootFolder.'tags.json'))

      //DEVELOPMENT : SHOW THE RESULT AND DIE SO DON'T MODIFY THE FILE
      if (($this->_DEVMODE) && ($old!=$markdown)) {
         echo $this->aeDebug->log('=== DEV MODE ENABLED ===',true);
         echo $this->aeDebug->log('= new file content ('.$fullname.') =',true);
         require_once("libs/Parsedown.php");$Parsedown=new Parsedown();$html=$Parsedown->text($markdown);echo $html;die();      
      }
      
      // Rewrite the file if a change has been done
      if ($old!=$markdown) {
         // rewrite the file
         if ($handle = fopen($fullname,'w')) {
            fwrite($handle, $markdown);
            fclose($handle);		
         }    
      }
      
      //
      // -----------------------------------------------------------------------
      
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

      $fnameHTMLrel=str_replace(str_replace('/',DS,$this->_rootFolder),'',$fnameHTML);      

      // Generate the URL (full) to the html file, f.i. http://localhost/docs/folder/file.html
      $tmp = rtrim(aeSecureFct::getCurrentURL(FALSE,TRUE),'/').'/'.str_replace(DS,'/',$fnameHTMLrel);

      // Open new window icon
//if($this->_saveHTML===TRUE) $icons.='<i id="icon_window" data-task="window" data-file="'.utf8_encode(str_replace(DS,'/',$tmp)).'" class="fa fa-external-link" aria-hidden="true" title="'.$this->getText('open_html','OPEN_HTML').'"></i>';
      if($this->_saveHTML===TRUE) $icons.='<i id="icon_window" data-task="window" data-file="'.utf8_encode($tmp).'" class="fa fa-external-link" aria-hidden="true" title="'.$this->getText('open_html','OPEN_HTML').'"></i>';
      
      // Edit icon : only if an editor has been defined
      if ($this->_settingsEditAllowed==TRUE) {
         $icons.='<i id="icon_edit" data-task="edit" class="fa fa-pencil-square-o" aria-hidden="true" title="'.$this->getText('edit_file','EDIT_FILE').'" data-file="'.$filename.'"></i>';
      }

      require_once("libs/Parsedown.php");
      $Parsedown=new Parsedown();      
      $html=$Parsedown->text($markdown);
      
      // -------------------------------------------------------------------------------
      // 
      // Check the presence of tags i.e. things like §tag, §frama, §webdev, ...
      // The § sign followed by a word
      
      $matches = array();

      preg_match_all('/'.PREFIX_TAG.'([a-zA-Z0-9]+)/', $html, $matches);
      // If matches is greater than zero, there is at least one <encrypt> tag found in the file content

      if (count($matches[1])>0) self::StoreTags($matches[1]);
  
      //
      // -------------------------------------------------------------------------------
      
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
      
      // Retrieve the URL to this note
      $thisNote= urldecode(aeSecureFct::getCurrentURL(FALSE,FALSE));

      // Keep only the script name and querystring so remove f.i. http://localhost/notes/
      //$thisNote=str_replace(aeSecureFct::getCurrentURL(FALSE,TRUE),'',$thisNote);
      
      $html=str_replace('</h1>', '</h1><div id="icons" class="onlyscreen fa-3x">'.
         '<i id="icon_printer" data-task="printer" class="fa fa-print" aria-hidden="true" title="'.str_replace("'", "\'", self::getText('print_preview','PRINT_PREVIEW')).'"></i>'.
         '<i id="icon_clipboard" data-task="clipboard" class="fa fa-clipboard" data-clipboard-text="'.$thisNote.'" aria-hidden="true" title="'.str_replace("'", "\'", self::getText('copy_link','COPY_LINK')).'"></i>'.
         '<i id="icon_slideshow" data-task="slideshow" data-file="'.$filename.'" class="fa fa-desktop" aria-hidden="true" title="'.str_replace("'", "\'", self::getText('slideshow','SLIDESHOW')).'"></i>'.        
         $icons.'</div>',$html);
      $html=str_replace('src="images/', 'src="'.DOC_FOLDER.'/'.str_replace(DS,'/',dirname($filename)).'/images/',$html);
      $html=str_replace('href="files/', 'href="'.DOC_FOLDER.'/'.str_replace(DS,'/',dirname($filename)).'/files/',$html);
      $html='<div class="onlyscreen filename">'.utf8_encode($fullname).'</div>'.$html.'<hr/>';
      
      // LazyLoad images ? 
      if ($this->_OptimizeLazyLoad==1) {
         $html=str_replace('<img src=','<img class="lazyload" data-src=',$html);
      }

      return $html;
	  
   } // function ShowFile()
   
   /**
    * Return the translation of a given text
    * @param string $variable
    */
   private function getText(string $variable, string $default) : string {
      
      $return='';   
      
      if (isset($this->_json['languages'][$this->_settingsLanguage])) {
         $lang=&$this->_json['languages'][$this->_settingsLanguage];
         $return=isset($lang[$variable]) ? $lang[$variable] : '';
      }
      
      if (($return=='') && (file_exists($fname=dirname(__DIR__).DS.'settings.json.dist'))) {

         $json=$this->aeJSON->json_decode($fname,true);

         $lang=&$json['languages'][$this->_settingsLanguage];
         $return=isset($lang[$variable]) ? $lang[$variable] : '';
      }
      
      if ($return=='') $return=(trim($default)!=='' ? constant($default) : '');
      
      return $return;
      
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

      $return=array();
      if (trim($keywords)=='') return $return;
      
      if ($this->_DEBUGMODE) {
         $return['debug'][]=$this->aeDebug->log('Search',true);
         $return['debug'][]=$this->aeDebug->log('Search for ['.str_replace(",",", ",$keywords).']',true);    
      }
      
      // $keywords can contains multiple terms like 'invoices,2017,internet'.  
      // Search for these three keywords (AND)
      $keywords=explode(',',rtrim($keywords,','));
            
      $arrFiles=array_unique(aeSecureFiles::rglob('*.md',$this->_rootFolder.$this->_settingsDocsFolder));
      
      if (count($arrFiles)==0) return null;

      // Be carefull, folders / filenames perhaps contains accentuated characters
      $arrFiles=array_map('utf8_encode', $arrFiles);
      
      // Sort, case insensitve
      natcasesort($arrFiles);   
     
      // Initialize the encryption class
      $aesEncrypt=new aeSecureEncrypt($this->_encryptionPassword, $this->_encryptionMethod);

      //$return['keywords']=$keywords;
      foreach ($arrFiles as $file) {
         
         // Don't mention the full path, should be relative for security reason
         $file=str_replace($this->_rootFolder.$this->_settingsDocsFolder,'',$file);
         
         // If the keyword can be found in the document title, yeah, it's the fatest solution,
         // return that filename
         
         foreach($keywords as $keyword) {
            $bFound=TRUE;             
            if (stripos($file, $keyword)===FALSE) {
               // at least one term is not present in the filename, stop            
               $bFound=FALSE;
               break;
            }            
         } // foreach($keywords as $keyword)
         
         if ($bFound) {
            
            if ($this->_DEBUGMODE) $return['debug'][]=$this->aeDebug->log('All keywords found in filename : ['.$file.']',true);    
            
            // Found in the filename => stop process of this file
            $return['files'][]=$file;
            
         } else { // if ($bFound)
                     
            // Open the file and check against its content
            
            $fullname=utf8_decode($this->_rootFolder.$this->_settingsDocsFolder.$file);
            $content=file_get_contents($fullname);
            
            $bFound=TRUE;
            
            foreach($keywords as $keyword) {               
               if (stripos($content, $keyword)===FALSE) {
                  // at least one term is not present in the content (unencrypted), stop   
                  $bFound=FALSE;
                  break;
               }            
            } // foreach($keywords as $keyword)
            
            if ($bFound) {
            
               if ($this->_DEBUGMODE) $return['debug'][]=$this->aeDebug->log('All keywords found in unencrypted filecontent : ['.$file.']',true);  
               
               // Found in the filename => stop process of this file
               $return['files'][]=$file;
            
            } else { // if ($bFound)
  
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
                  
                  } // for($i;$i<$j;$i++)
                  
                  $bFound=TRUE;

                  foreach($keywords as $keyword) {               
                     if (stripos($content, $keyword)===FALSE) { 
                        // at least one term is not present in the encrypted content, stop   
                        $bFound=FALSE;
                        break;
                     }  
                  } // foreach($keywords as $keyword)    
                  
               } // if (count($matches[1])>0) {
                  
               if($bFound) {
                  if ($this->_DEBUGMODE) $return['debug'][]=$this->aeDebug->log('All keywords found in unencrypted filecontent : ['.$file.']',true);  
                  $return['files'][]=$file;
               }
               
            } // if ($bFound)
           
         } // if ($bFound) {
            
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
      die(__METHOD__.' should be coded');
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
         
         if ($this->_useCache==TRUE) {
            
            // Define metadata for the cache
            $cache='<meta http-equiv="cache-control" content="max-age=0" /><meta http-equiv="cache-control" content="no-cache" /><meta http-equiv="expires" content="0" />'.
               '<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" /><meta http-equiv="pragma" content="no-cache" />';
            $html=str_replace('<!--%META_CACHE%-->', $cache, $html);
         } else {
            $html=str_replace('<!--%META_CACHE%-->', '', $html);
         }
         
         $html=str_replace('%APP_NAME%', APP_NAME, $html);
         $html=str_replace('%APP_NAME_64%', base64_encode(APP_NAME), $html);
         $html=str_replace('%IMG_MAXWIDTH%', self::getSetting('ImgMaxWidth','800'), $html);
         
         $html=str_replace('%EDT_SEARCH_PLACEHOLDER%', self::getText('search_placeholder','SEARCH_PLACEHOLDER'), $html);
         $html=str_replace('%EDT_SEARCH_MAXLENGTH%', SEARCH_MAX_LENGTH, $html);
         
         // Define the global markdown variable.  Used by the assets/js/markdown.js script
         $JS=
            "\nvar markdown = {};\n".
            "markdown.message={};\n".
            "markdown.message.allow_popup_please='".str_replace("'","\'",self::getText('allow_popup_please','ALLOW_POPUP_PLEASE'))."';\n".
            "markdown.message.apply_filter='".str_replace("'","\'",self::getText('apply_filter','APPLY_FILTER'))."';\n".
            "markdown.message.apply_filter_tag='".str_replace("'","\'",self::getText('apply_filter_tag','APPLY_FILTER_TAG'))."';\n".               
            "markdown.message.copy_link_done='".str_replace("'","\'",self::getText('copy_link_done','COPY_LINK_DONE'))."';\n".                              
            "markdown.message.display_that_note='".str_replace("'","\'",self::getText('display_that_note','DISPLAY_THAT_NOTE'))."';\n".                              
            "markdown.message.filesfound='".str_replace("'","\'",self::getText('files_found','FILES_FOUND'))."';\n".
            "markdown.message.pleasewait='".str_replace("'","\'",self::getText('please_wait','PLEASE_WAIT'))."';\n".
            "markdown.message.search_no_result='".str_replace("'","\'",self::getText('search_no_result','SEARCH_NO_RESULT'))."';\n".
            "markdown.url='index.php';\n".
            "markdown.settings={};\n".
            "markdown.settings.auto_tags='".implode($this->_arrTagsAutoSelect,",")."';\n".
            "markdown.settings.debug=".($this->_DEBUGMODE?1:0).";\n".
            "markdown.settings.development=".($this->_DEVMODE?1:0).";\n".               
            "markdown.settings.lazyload=".$this->_OptimizeLazyLoad.";\n".
            "markdown.settings.prefix_tag='".PREFIX_TAG."';\n";
            "markdown.settings.search_max_width=".SEARCH_MAX_LENGTH.";";
         
         $html=str_replace('%MARKDOWN_GLOBAL_VARIABLES%', $JS, $html);
         
         // if any, output the code for the Google Font (see settings.json)
         $html=str_replace('<!--%FONT%-->', self::GoogleFont(), $html);

         // if present, add your custom stylesheet if the custom.css file is present. That file should be present in the root folder; not in /assets/js
         $html=str_replace('<!--%CUSTOM_CSS%-->', aeSecureFct::addStylesheet('custom.css'), $html);


         // Additionnal javascript, depends on user's settings
         $sAdditionnalJS='';
         if ($this->_OptimizeLazyLoad==1) $AdditionnalJS='<script type="text/javascript" src="libs/lazysizes/lazysizes.min.js"></script> ';
         
         $html=str_replace('<!--%ADDITIONNAL_JS%-->', $AdditionnalJS, $html);
         
         // if present, add your custom javascript if the custom.js file is present. That file should be present in the root folder; not in /assets/js
         $html=str_replace('<!--%CUSTOM_JS%-->', aeSecureFct::addJavascript('custom.js'), $html);
         
         return $html;
         
      } else { // if (is_file($template=dirname(__DIR__).'/templates/main.php'))
         
         self::ShowError(str_replace('%s','<strong>'.$template.'</strong>',$this->getText('file_not_found','FILE_NOT_FOUND')),TRUE);

      } // if (is_file($template=dirname(__DIR__).'/templates/main.php'))
      
   } // function showInterface()   
   
   /**
    * 
    * @param string $filename
    * @return string
    */
   private function getSlideshow(string $filename) : string {
    
      if ($filename!="") {
         
         $fullname=utf8_decode($this->_rootFolder.$this->_settingsDocsFolder.$filename);

         if (!file_exists($fullname)) {
            self::ShowError(str_replace('%s','<strong>'.$fullname.'</strong>',$this->getText('file_not_found','FILE_NOT_FOUND')),TRUE);
         }

         $markdown=file_get_contents($fullname);
		 
         // ------------------------------------------------------------------
         // Remove <encrypt xxxx> content </encrypt>
         // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
         preg_match_all('/<encrypt[[:blank:]]*[^>]*>([\\S\\n\\r\\s]*?)<\/encrypt>/', $markdown, $matches);
         
         // Remove the tag prefix
         $markdown=str_replace(PREFIX_TAG,'',$markdown);

         // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
         if (count($matches[0])>0) {

            $j=count($matches[0]);

            $i=0;

            for($i;$i<$j;$i++) {
               $markdown=str_replace($matches[0][$i], '<strong class="confidential">'.$this->getText('confidential','CONFIDENTIAL').'</strong>', $markdown);
            }
         }
         
         //
         // ------------------------------------------------------------------
		 	
         // Try to retrieve the heading 1
       
         preg_match("/# (.*)/", $markdown, $matches);   
         $pageTitle = (count($matches)>0) ? $matches[1] : '';
         
         // Consider that every Headings 2 and 3 should start in a new slide
         // The "remark" library allow indeed to give a name to each slide by just adding "name: NAME" in the markdown string
		 
         // Get every heading 2 (i.e. lines starting with "## TITLE") and heading 3 ("### Subtitle")
		 
         $arrHeading=array('##','###');
         foreach ($arrHeading as $head) {

            $matches=array();	
			
            preg_match_all("/\\n".$head." (.*)/", $markdown, $matches);
			
            if(count($matches)>0) {

			   // Process one by one
               $j=count($matches[0]);
			   
               for ($i=0;$i<$j;$i++) {
				
                  // $matches[0][$i] is f.i. "## TITLE" while $matches[1][$i] is "TITLE"
                  // 
                  // remark allow to specify the name of the slide so add a "name:" property in the markdown like this : 
                  //
                  //   name: TITLE
                  //   ---
                  //   ## TITLE
			   
                  $markdown=str_replace($matches[0][$i],
                        //"???".PHP_EOL.str_replace('/',DS,$filename).PHP_EOL.  // Add speaker note : ??? followed by a line and the text
                        "---".PHP_EOL.
                        "name: ".$matches[1][$i].PHP_EOL.
                        ".footnote[.italic[".$pageTitle."]]".PHP_EOL.
                        $matches[0][$i], $markdown);
			   
               } // for ($i)

            } // if(count($matches)>0)

         } // foreach ($arrHeading as $head)
		 
		 //
         // --------------------------------------------------------------------------------
		 
         $slideshow=file_get_contents(dirname(__DIR__).'/templates/slideshow.php');
         
         $html=str_replace('<!--%SOURCE%-->',$markdown,$slideshow);
         $html=str_replace('<!--%URL%-->',rtrim(aeSecureFct::getCurrentURL(FALSE,TRUE),'/'),$html);
         $html=str_replace('<--%TITLE%-->',$pageTitle,$html);
 
         // Store that HTML to the server
         $fnameHTML=str_replace('.md','_slideshow.html',$fullname);

         if ($handle = fopen($fnameHTML,'w+')) {
            fwrite($handle,$html);
            fclose($handle);		
         }
     
         // And return an URL to that file
         $tmp = str_replace('\\','/',rtrim(aeSecureFct::getCurrentURL(FALSE,TRUE),'/').str_replace(dirname($_SERVER['SCRIPT_FILENAME']),'',$fnameHTML));
         return utf8_encode($tmp);
         
      } else { // if ($filename!="")
         
         return '';
         
      } // if ($filename!="")
      
   } // function getSlideshow()
   
   /**
    * Entry point of this class, run a task
    * 
    * @param string $task
    */
   public function process(string $task) {
      
      switch ($task) {

         case 'display':

            header('Content-Type: text/html; charset=utf-8'); 

            $fname=json_decode(urldecode(aeSecureFct::getParam('param','string','',true)));

            $result=self::ShowFile($fname);
            echo $result;
            die();

         case 'edit' : 

            $fname=json_decode(urldecode(aeSecureFct::getParam('param','string','',true)));
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
            
         case 'slideshow':            
            
            header('Content-Type: application/json');
            $fname=json_decode(urldecode(aeSecureFct::getParam('param','string','',true)));
            echo json_encode(self::getSlideshow($fname), JSON_PRETTY_PRINT);
            die();
            
         case 'tags':            
            
            header('Content-Type: application/json'); 
            echo json_encode(self::getTags(), JSON_PRETTY_PRINT); 
            die();
            
         default :  // task=main => display the main interface
            
            echo self::showInterface();
            die();

      } // switch ($task)
   
   } // function process()
   
} // class aeSecureMarkdown