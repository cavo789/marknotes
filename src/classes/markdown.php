<?php
/* REQUIRES PHP 7.x AT LEAST */

namespace AeSecure;

// Requires PHP 7.x
         
class Markdown
{
    
    private $aeSettings=null;
      
   /**
    * Class constructor : initialize a few private variables
    *
    * @param string $folder    Root folder of the website (f.i. "C:\Christophe\Documents\").
    * @return boolean
    */
    function __construct(string $folder = '')
    {
        require_once('constants.php');

        // Get the root folder and be sure the folder ends with a slash
        // Respect the directory separator (which is "\" on Windows system)
        if (trim($folder)=='') {
            $folder=str_replace('/', DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_FILENAME']));
        }
        $folder=rtrim($folder, DS).DS;
        
        if (!class_exists('Settings')) {
            require_once 'settings.php';
        }
                
        $this->aeSettings=\AeSecure\Settings::getInstance($folder);
        
        if (!class_exists('Encrypt')) {
            require_once 'encrypt.php';
        }
        
        if (!class_exists('Files')) {
            require_once 'files.php';
        }
 
        if (!class_exists('Fct')) {
            require_once 'functions.php';
        }
        
        if (!class_exists('JSON')) {
            require_once 'json.php';
        }
    
        return true;
    } // function __construct()
   
    /**
    * Entry point of this class, run a task
    *
    * @param string $task
    */
    public function process(string $task)
    {
      
        $filename=json_decode(urldecode(Functions::getParam('param', 'string', '', true)));
        
        if ($filename!='') {
            $filename=\AeSecure\Files::sanitizeFileName(trim($filename));
        }
        
        switch ($task) {
            case 'clean':
                // Remove html files.  These files aren't needed, only .md files are important
                
                require_once(TASKS.'clean.php');
                \AeSecureMDTasks\Clean::Run();
                break;
               
            case 'delete':
                // Delete a note or a folder
                
                $type=\AeSecure\Functions::getParam('param3', 'string', '', false);

                require_once(TASKS.'delete.php');
                \AeSecureMDTasks\Delete::Run(array('filename'=>$filename,'type'=>$type));
                break;
            
            case 'display':
                // Display the HTML rendering of a note
                
                require_once(TASKS.'display.php');
                \AeSecureMDTasks\Display::Run(array('filename'=>$filename));
                break;
               
            case 'edit':
                // Edit an existing file
                
                require_once(TASKS.'edit.php');
                \AeSecureMDTasks\Edit::Run(array('filename'=>$filename));
                break;
                
            case 'listFiles':
                // Retrieve the list of .md files.
                
                require_once(TASKS.'listfiles.php');
                echo \AeSecureMDTasks\ListFiles::Run();
                break;
            
            case 'pdf':
                // Generate a PDF
                require_once(TASKS.'pdf.php');
                \AeSecureMDTasks\PDF::Run(array('filename'=>$filename));
                break;
                
            case 'rename':
                // Add/rename file/folder
                
                $newname=json_decode(urldecode(\AeSecure\Functions::getParam('param2', 'string', '', true)));
                if ($newname!='') {
                    $newname=\AeSecure\Files::sanitizeFileName(trim($newname));
                }
                $type=\AeSecure\Functions::getParam('param3', 'string', '', false);
                  
                 // Remove html files.  These files aren't needed, only .md files are important
                require_once(TASKS.'addorrename.php');
                \AeSecureMDTasks\AddOrRename::Run(array('oldname'=>$filename,'newname'=>$newname,'type'=>$type));
                break;
              
            case 'save':
                // Save new content (after edition by the user)
                
                $markdown=json_decode(urldecode(\AeSecure\Functions::getParam('markdown', 'string', '', true)));
                
                require_once(TASKS.'save.php');
                \AeSecureMDTasks\Save::Run(array('filename'=>$filename,'markdown'=>$markdown));
                break;
                
            case 'search':
                // Search for one or more keywords in notes
                
                $pattern=\AeSecure\Functions::getParam('param', 'string', '', true, $this->aeSettings->getSearchMaxLength());
                
                require_once(TASKS.'search.php');
                \AeSecureMDTasks\Search::Run(array('pattern'=>$pattern));
                break;
                
            case 'slideshow':
                // Display the "slideshow" version of the note
                
                require_once(TASKS.'slideshow.php');
                \AeSecureMDTasks\SlideShow::Run(array('filename'=>$filename));
                break;
                    
            case 'tags':
                // Get the list of folders/tags
                
                require_once(TASKS.'tags.php');
                \AeSecureMDTasks\Tags::Run();
                break;
                
            default:
                // Show the main interface
                require_once(TASKS.'showinterface.php');
                echo \AeSecureMDTasks\ShowInterface::Run();
                break;
        } // switch ($task)
    } // function process()
} // class Markdown
