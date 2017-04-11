<?php
/* REQUIRES PHP 7.x AT LEAST */

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Markdown
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Markdown();
        }

        return self::$_instance;
    }
    /**
    * Entry point of this class, run a task
    *
    * @param string $task
    * @param string $filename   Optional, if not mentionned, get this information from $_POST
    *
    */
    public function process(string $task, string $filename = '', array $params = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $aeSession = \MarkNotes\Session::getInstance();
        $aeSession->extend();

        if ($filename === '') {
            $filename = json_decode(urldecode($aeFunctions->getParam('param', 'string', '', true)));
        }

        if ($filename != '') {
            $filename = $aeFiles->sanitizeFileName(trim($filename));
        }

        $docRoot = $aeSettings->getFolderDocs(false);

        // The filename shouldn't mention the docs folders, just the filename
        // So, $filename should not be docs/markdown.md but only markdown.md because the
        // folder name will be added later on
        if (substr($filename, 0, strlen($docRoot)) === $docRoot) {
            $filename = substr($filename, strlen($docRoot));
        }

        if ($params === null) {
            $params = array();
        }

        if (!isset($params['filename'])) {
            $params['filename'] = $filename;
        }

        if (!isset($params['task'])) {
            $params['task'] = $task;
        }

        switch ($task) {
            case 'clear':
                // Clear the session object
                $aeTask = \MarkNotes\Tasks\ClearCache::getInstance();
                header('Content-Type: application/json');
                echo $aeTask->run();
                break;

            case 'delete':
                // Delete a note or a folder : retrieve the type
                $type = $aeFunctions->getParam('param3', 'string', '', false);
                $aeTask = \MarkNotes\Tasks\Delete::getInstance();
                header('Content-Type: text/html; charset=utf-8');
                echo $aeTask->run(array('filename' => $filename,'type' => $type));
                break;

            case 'display':
                // Display the HTML rendering of a note
                $aeTask = \MarkNotes\Tasks\Display::getInstance();
                header('Content-Type: text/html; charset=utf-8');
                echo $aeTask->run($params);
                break;

            case 'edit':
                // Edit an existing file
                $aeTask = \MarkNotes\Tasks\Edit::getInstance();
                echo $aeTask->run(array('filename' => $filename));
                break;

            case 'getTimeline':
                // Get the list of notes
                $aeTask = \MarkNotes\Tasks\Timeline::getInstance();
                header('Content-Type: application/json');
                echo $aeTask->getJSON($params);
                break;

            case 'listFiles':
                // Retrieve the list of .md files.
                $aeTask = \MarkNotes\Tasks\ListFiles::getInstance();
                header('Content-Type: application/json');
                echo \MarkNotes\Tasks\ListFiles::run();
                break;

            case 'login':
                // Login
                $aeTask = \MarkNotes\Tasks\Login::getInstance();
                header('Content-Type: application/json');
                echo $aeTask->run();
                break;

            case 'pdf':
                // Generate a PDF
                $aeTask = \MarkNotes\Tasks\PDF::getInstance();

                $fPDF = $aeTask->run($params);

                // Send the pdf to the browser ... only if successfully created
                if (($fPDF !== '') && $aeFiles->fileExists($fPDF)) {
                    $aeTask->download($fPDF);
                } else {
                    header("HTTP/1.0 404 Not Found");
                    echo "Error during the creation of the PDF.".PHP_EOL.
                        "File [".$fPDF."] is missing";
                }

                break;

            case 'rename':
                // Add/rename file/folder

                $newname = json_decode(urldecode($aeFunctions->getParam('param2', 'string', '', true)));
                if ($newname != '') {
                    $newname = $aeFiles->sanitizeFileName(trim($newname));
                }
                $type = $aeFunctions->getParam('param3', 'string', '', false);

                // Remove html files.  These files aren't needed, only .md files are important
                $aeTask = \MarkNotes\Tasks\AddOrRename::getInstance();

                header('Content-Type: application/json');
                echo $aeTask->run(array('oldname' => $filename,'newname' => $newname,'type' => $type));
                break;

            case 'save':
                // Save new content (after edition by the user)

                $markdown = json_decode(urldecode($aeFunctions->getParam('markdown', 'string', '', true)));

                $aeTask = \MarkNotes\Tasks\Save::getInstance();
                header('Content-Type: application/json');
                echo $aeTask->run(array('filename' => $filename,'markdown' => $markdown));
                break;

            case 'search':
                // Search for one or more keywords in notes
                $pattern = $aeFunctions->getParam('str', 'string', '', false, $aeSettings->getSearchMaxLength());
                header('Content-Type: application/json');
                $aeTask = \MarkNotes\Tasks\Search::getInstance();
                echo $aeTask->run(array('pattern' => $pattern));
                break;

            case 'sitemap':
                // Display a sitemap
                $aeTask = \MarkNotes\Tasks\Sitemap::getInstance();
                header('Content-Type: application/xml; charset=utf-8');
                echo $aeTask->run();
                break;

            case 'slideshow':
                // Display the "slideshow" version of the note
                $aeTask = \MarkNotes\Tasks\SlideShow::getInstance();
                header('Content-Type: text/html; charset=utf-8');
                echo $aeTask->run($params);
                break;

            case 'tags':
                // Get the list of folders/tags
                $aeTask = \MarkNotes\Tasks\Tags::getInstance();
                header('Content-Type: application/json');
                echo $aeTask->run();
                break;

            case 'timeline':
                // Display a timeline of all articles
                $aeTask = \MarkNotes\Tasks\Timeline::getInstance();
                header('Content-Type: text/html; charset=utf-8');
                echo $aeTask->run($params);
                break;

            default:
                $aeTask = \MarkNotes\Tasks\ShowInterface::getInstance();
                echo $aeTask->run();
                break;
        } // switch ($task)
    }
}
