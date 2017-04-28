<?php
/* REQUIRES PHP 7.x AT LEAST */

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Markdown
{
    protected static $hInstance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new Markdown();
        }

        return self::$hInstance;
    }
    /**
    * Entry point of this class, run a task
    *
    * @param string $task
    * @param string $filename   Optional, if not mentionned, get this information from $_POST
    *
    */
    public function process(string $task = '', string $filename = '', array $params = null)
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            $aeDebug->log('Run ['.$task.'] filename ['.$filename.']', 'debug');
        }
        /*<!-- endbuild -->*/

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

        // Remember somes variables into the server_session
        $aeSession->set('task', $task);
        $aeSession->set('filename', $filename);
        $aeSession->set('layout', ($params['layout'] ?? ''));

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

            case 'listFiles':
                // Retrieve the list of .md files.
                $aeTask = \MarkNotes\Tasks\ListFiles::getInstance();
                header('Content-Type: application/json');
                echo \MarkNotes\Tasks\ListFiles::run();
                break;

            case 'main':

                // Display the interface of marknotes, with the treeview
                // and the selected note content
                $aeTask = \MarkNotes\Tasks\ShowInterface::getInstance();
                echo $aeTask->run();
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

            default:

                // --------------------------------
                // Call task plugins
                $aeEvents->loadPlugins('task', $task);
                $args = array(&$params);
                $aeEvents->trigger('run.task', $args);
                // --------------------------------

                break;
        } // switch ($task)
    }
}
