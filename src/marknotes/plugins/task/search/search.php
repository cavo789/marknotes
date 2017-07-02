<?php

/**
 * Search engine, search for keywords in notes and return the md5 of the filename
 * Then, the treeview (jstree) will filter on that list and only show items with the same md5
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Search
{
    private static function getFiles() : array
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // get the list of files
        $arrFiles = array();
        if ($aeSettings->getOptimisationUseServerSession()) {
            // Get the list of files/folders from the session object if possible
            $arrFiles = json_decode($aeSession->get('SearchFileList', ''));
            if (!is_array($arrFiles)) {
                $arrFiles = array();
            }
        }

        if (count($arrFiles) == 0) {
            $arrFiles = $aeFunctions->array_iunique($aeFiles->rglob('*.md', $aeSettings->getFolderDocs(true)));

            // Sort, case insensitve
            natcasesort($arrFiles);

            if ($aeSettings->getOptimisationUseServerSession()) {
                // Remember for the next call
                $aeSession->set('SearchFileList', json_encode($arrFiles, JSON_PRETTY_PRINT));
            }
        }
        if (count($arrFiles) == 0) {
            return null;
        }

        return $arrFiles;
    }

    public static function run(&$params = null)
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeMarkdown = \MarkNotes\FileType\Markdown::getInstance();

        // String to search (can be something like 'invoices,2017,internet') i.e. multiple keywords
        $pattern = trim($aeFunctions->getParam('str', 'string', '', false, SEARCH_MAX_LENGTH));

        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            $aeDebug->log('Searching for ['.$pattern.']', 'debug');
        }
        /*<!-- endbuild -->*/

        // $keywords can contains multiple terms like 'invoices,2017,internet'.
        // Search for these three keywords (AND)
        $keywords = explode(',', rtrim($pattern, ','));

        // Retrieve the list of files
        $arrFiles = self::getFiles();

        // Do we need to encode accents ?
        $bEncodeAccents = boolval($aeSettings->getFiles('encode_accent', 0));
        if ($bEncodeAccents) {
            $arrFiles = array_map('utf8_encode', $arrFiles);
        }

        // docs should be relative so $aeSettings->getFolderDocs(false) and not $aeSettings->getFolderDocs(true)
        $docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

        $return = array();

        foreach ($arrFiles as $file) {

            // Don't mention the full path, should be relative for security reason
            $file = str_replace($aeSettings->getFolderDocs(true), '', $file);

            // If the keyword can be found in the document title, yeah, it's the fatest solution,
            // return that filename

            foreach ($keywords as $keyword) {
                $bFound = true;
                if (stripos($file, $keyword) === false) {
                    // at least one term is not present in the filename, stop
                    $bFound = false;
                    break;
                }
            }

            if ($bFound) {

                // Found in the filename => stop process of this file

                /*<!-- build:debug -->*/
                if ($aeSettings->getDebugMode()) {
                    $aeDebug->log('   FOUND IN ['.$docs.$file.']', 'info');
                }
                /*<!-- endbuild -->*/

                $return[] = md5($docs.$file);
            } else { // if ($bFound)

                // Open the file and check against its content (plain and encrypted)

                /*$bEncodeAccents = boolval($aeSettings->getFiles('encode_accent', 0));
                if (!$bEncodeAccents) {
                    $fullname = utf8_decode($aeSettings->getFolderDocs(true).$file);
                } else {*/
                    $fullname = $aeSettings->getFolderDocs(true).$file;
                /*}*/

                // Read the note content
                // The read() method will fires any plugin linked to the markdown.read event
                // so encrypted notes will be automatically unencrypted
                $content = $aeMarkdown->read($fullname);

                $bFound = true;

                foreach ($keywords as $keyword) {

                    // Add "$file" which is the filename in the content, just for the search.
                    // Because when f.i. search for two words; one can be in the filename and one in the content
                    // By searching only in the content; that file won't appear while it should be the Collapse
                    // so "fake" and add the filename in the content, just for the search_no_result

                    if (stripos($file.'#@#§§@'.$content, $keyword) === false) {
                        // at least one term is not present in the content, stop
                        $bFound = false;
                        break;
                    }
                } // foreach($keywords as $keyword)

                if ($bFound) {

                    /*<!-- build:debug -->*/
                    if ($aeSettings->getDebugMode()) {
                        $aeDebug->log('   FOUND IN ['.$docs.$file.']', 'info');
                    }
                    /*<!-- endbuild -->*/

                    // Found in the filename => stop process of this file
                    $return[] = md5($docs.$file);
                }  // if ($bFound)
            } // if ($bFound) {
        } // foreach ($arrFiles as $file)

        // Nothing should be returned, the list of files can be immediatly displayed
        header('Content-Type: application/json');
        echo json_encode($return, JSON_PRETTY_PRINT);

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // The search plugin is only for the interface so task="main" or by searching
        if (!in_array($task, array('main','search'))) {
            return false;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('run.task', __CLASS__.'::run');
        return true;
    }
}
