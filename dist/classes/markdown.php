<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-03-19T09:46:49.516Z
*/?>
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
    * @param  string $folder Root folder of the website (f.i. "C:\Christophe\Documents\").
    * @return boolean
    */
    function __construct(string $folder = '')
    {
        include_once 'constants.php';

        // Get the root folder and be sure the folder ends with a slash
        // Respect the directory separator (which is "\" on Windows system)
        if (trim($folder)=='') {
            $folder=str_replace('/', DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_FILENAME']));
        }
        $folder=rtrim($folder, DS).DS;

        if (!class_exists('Settings')) {
            include_once 'settings.php';
        }

        $this->aeSettings=\AeSecure\Settings::getInstance($folder);

        if (!class_exists('Encrypt')) {
            include_once 'encrypt.php';
        }

        if (!class_exists('Files')) {
            include_once 'files.php';
        }

        if (!class_exists('Fct')) {
            include_once 'functions.php';
        }

        if (!class_exists('JSON')) {
            include_once 'json.php';
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

                include_once TASKS.'clean.php';
                \AeSecureMDTasks\Clean::Run();
                break;

            case 'delete':
                // Delete a note or a folder

                $type=\AeSecure\Functions::getParam('param3', 'string', '', false);

                include_once TASKS.'delete.php';
                \AeSecureMDTasks\Delete::Run(array('filename'=>$filename,'type'=>$type));
                break;

            case 'display':
                // Display the HTML rendering of a note

                include_once TASKS.'display.php';
                \AeSecureMDTasks\Display::Run(array('filename'=>$filename));
                break;

            case 'edit':
                // Edit an existing file

                include_once TASKS.'edit.php';
                \AeSecureMDTasks\Edit::Run(array('filename'=>$filename));
                break;

            case 'listFiles':
                // Retrieve the list of .md files.

                include_once TASKS.'listfiles.php';
                echo \AeSecureMDTasks\ListFiles::Run();
                break;

            case 'pdf':
                // Generate a PDF
                include_once TASKS.'pdf.php';
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
                include_once TASKS.'addorrename.php';
                \AeSecureMDTasks\AddOrRename::Run(array('oldname'=>$filename,'newname'=>$newname,'type'=>$type));
                break;

            case 'save':
                // Save new content (after edition by the user)

                $markdown=json_decode(urldecode(\AeSecure\Functions::getParam('markdown', 'string', '', true)));

                include_once TASKS.'save.php';
                \AeSecureMDTasks\Save::Run(array('filename'=>$filename,'markdown'=>$markdown));
                break;

            case 'search':
                // Search for one or more keywords in notes

                $pattern=\AeSecure\Functions::getParam('str', 'string', '', false, $this->aeSettings->getSearchMaxLength());
                //$pattern=\AeSecure\Functions::getParam('param', 'string', '', true, $this->aeSettings->getSearchMaxLength());

                include_once TASKS.'search.php';
                \AeSecureMDTasks\Search::Run(array('pattern'=>$pattern));
                break;

            case 'slideshow':
                // Display the "slideshow" version of the note

                include_once TASKS.'slideshow.php';
                \AeSecureMDTasks\SlideShow::Run(array('filename'=>$filename));
                break;

            case 'tags':
                // Get the list of folders/tags

                include_once TASKS.'tags.php';
                \AeSecureMDTasks\Tags::Run();
                break;

            default:
                // Show the main interface
                include_once TASKS.'showinterface.php';
                echo \AeSecureMDTasks\ShowInterface::Run();
                break;
        } // switch ($task)
    } // function process()
} // class Markdown
