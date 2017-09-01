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

        // No task has been mentionned, get the default one
        // this is the case when the user reach an URL like
        //   http://localhost/marknotes/docs/CMS/document
        // (where document is a .md file; the default task will be 'reveal')

        if (trim($task) === '') {
            $task = $aeSettings->getTask()['default'] ?? 'reveal';
        }

        $aeSession = \MarkNotes\Session::getInstance();
        $aeSession->extend();

        if ($filename === '') {
			$tmp=urldecode($aeFunctions->getParam('param', 'string', '', true));
			$filename=((json_decode($tmp)!='')?json_decode($tmp):$tmp);
        }

        if ($filename != '') {

			$filename = str_replace('/',DS,$filename);

            // The filename shouldn't mention the docs folders, just the filename
            // So, $filename should not be docs/markdown.md but only markdown.md because the
            // folder name will be added later on

            $docRoot = $aeSettings->getFolderDocs(false);

			if ($aeFunctions->startsWith($filename, $docRoot)) {
                $filename = substr($filename, strlen($docRoot));
            }

			// If the filename doesn't mention the file's extension, add it.
            if (substr($filename, -3) != '.md') {
                $filename .= '.md';
            }

			$filename=$aeFiles->removeExtension($filename).'.md';
			$full=$aeSettings->getFolderDocs(true).$filename;

            if (!file_exists($filename)) {

                // Do we need to encode accent on that system ?
                $bEncodeAccents = boolval($aeSettings->getFiles('encode_accent', 0));
                if (!$bEncodeAccents) {
                    $filename = utf8_decode($filename);
                }
            }

            $filename = $aeFiles->sanitizeFileName(trim($filename));
        }

        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            $aeDebug->log('Running task ['.$task.']', 'debug');
            $aeDebug->log('Run ['.$task.'] filename ['.$filename.']', 'debug',3);
        }
        /*<!-- endbuild -->*/

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

        // Process "core" tasks i.e. not part of a plugin
        switch ($task) {

            case 'display':
                // Display the HTML rendering of a note
                $aeTask = \MarkNotes\Tasks\Display::getInstance();
                header('Content-Type: text/html; charset=utf-8');
                echo $aeTask->run($params);
                break;

            case 'index':
                // Displan a dynamic index page
                $aeTask = \MarkNotes\Tasks\Index::getInstance();
                header('Content-Type: text/html; charset=utf-8');
                echo $aeTask->run($params);
                break;

            case 'listFiles':

                // Retrieve the list of .md files.
                $aeTask = \MarkNotes\Tasks\ListFiles::getInstance();
                $return = \MarkNotes\Tasks\ListFiles::run();

                // Set the header after the run() method so, if an error occurs, the error
                // will be displayed as a html string and not a json one

                header('Content-Type: application/json; charset=UTF-8');
                header('Content-Transfer-Encoding: ascii');
                echo $return;

                break;

            case 'main':

                // Display the interface of marknotes, with the treeview
                // and the selected note content
                $aeTask = \MarkNotes\Tasks\ShowInterface::getInstance();
                echo $aeTask->run();
                break;

            case 'md':

                // Display a .md file, call plugins and output note's content
                $filename = $aeSettings->getFolderDocs(true).$filename;

                $aeMarkdown = \MarkNotes\FileType\Markdown::getInstance();
                $content = $aeMarkdown->read($filename);

                header('Content-Type: text/markdown; charset=UTF-8');
                header('Content-Transfer-Encoding: ascii');
                echo $content;

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
