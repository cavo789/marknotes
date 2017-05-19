<?php

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Files
{

    /**
     * Be sure that filenames doesn't already start with the /docs folder (otherwise will
     * be mentionned twice)
     */
    private static function cleanUp(array &$params) : bool
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

        $docs = $aeSettings->getFolderDocs(false);

        // oldname is the actual file/folder name, before renaming it f.i.
        if (isset($params['oldname'])) {
            if (substr($params['oldname'], 0, strlen($docs)) == $docs) {
                $params['oldname'] = substr($params['oldname'], strlen($docs));
            }
            $params['oldname'] = $aeSettings->getFolderDocs(true).$params['oldname'];
        }

        // filename is the new file/folder name or the name of the newly created file/folder
        if (isset($params['filename'])) {
            if (substr($params['filename'], 0, strlen($docs)) == $docs) {
                $params['filename'] = substr($params['filename'], strlen($docs));
            }
            $params['filename'] = $aeSettings->getFolderDocs(true).$params['filename'];
        }

        return true;
    }

    /**
     * Convert the $arr parameter into a json object and return the string
     */
    private static function returnInfo(array $arr) : string
    {
        $aeJSON = \MarkNotes\JSON::getInstance();
        return $aeJSON->json_return_info($arr, false);
    }

    /**
     * Create a note on the disk
     */
    private static function createFile(string $filename) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFilesystem = \MarkNotes\Plugins\Task\Files\Helpers\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // Sanitize
        $filename = $aeFiles->removeExtension($filename).'.md';

        // Try to create a file called "$filename.md" on the disk
        $wReturn = $aeFilesystem->createFile($filename);

        switch ($wReturn) {
            case CREATE_SUCCESS:
                $code = 'file_created';
                break;
            case ALREADY_EXISTS:
                $code = 'file_already_exists';
                break;
            default:
                $code = 'error_create_file';
                break;
        }

        $msg = str_replace(
            '%s',
            str_replace($aeSettings->getFolderDocs(true), '', $filename),
            $aeSettings->getText($code)
        );

        $arr = array(
            'status' => (($wReturn === CREATE_SUCCESS) ? 1 : 0),
            'action' => 'create',
            'type' => 'file',
            'msg' => $msg,
            'filename' => $filename
        );

        $return = self::returnInfo($arr);
    }

    /**
     * Create or Rename a file / folder
     */
    private static function createRename(array &$params = null) : string
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // file or folder
        $type = $aeFunctions->getParam('type', 'string', '', false);

        $newname = trim(json_decode(urldecode($aeFunctions->getParam('param', 'string', '', true))));
        if ($newname != '') {
            $newname = $aeFiles->sanitizeFileName(trim($newname));
        }

        //$oldname = utf8_encode($params['oldname']);
        $oldname = trim(json_decode(urldecode($aeFunctions->getParam('oldname', 'string', '', true))));
        if ($oldname != '') {
            $oldname = $aeFiles->sanitizeFileName(trim($oldname));

            if (mb_detect_encoding($oldname)) {
                if (!file_exists($oldname)) {
                    $oldname = utf8_decode($oldname);
                }
            }
        }

        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            $aeDebug->log(__METHOD__, 'debug');
            $aeDebug->log('Oldname=['.$oldname.']', 'debug');
            $aeDebug->log('Newname=['.$newname.']', 'debug');
            $aeDebug->log('Type=['.$type.']', 'debug');
        }
        /*<!-- endbuild -->*/

        if ((trim($type) === '') || (trim($newname) === '')) {
            echo $aeFunctions->showError('unknown_error', 'An error has occured, please try again', false);
            return false;
        }

        $return = '';

        if ($type === 'folder') {
            // it's a folder

            $aeFilesystem = \MarkNotes\Plugins\Task\Files\Helpers\Folders::getInstance();

            if ($oldname === '') {
                die("NEW FOLDER - Died in ".__FILE__.", line ".__LINE__);
            } else {
                die("RENAME FOLDER - Died in ".__FILE__.", line ".__LINE__);
            }
        } else {
            
            // it's a file

            if ($oldname === '') {
                $return = self::createFile($newname);
            } else {
                die("RENAME FILE [".$oldname."] - Died in ".__FILE__.", line ".__LINE__);
            }

            if ($return === false) {
                $return = str_replace(
                    '%s',
                    $newname,
                    $aeSettings->getText('error_create_file', 'An error has occured during the creation of the note [%s]')
                );
            }
        }

        //header('Content-Type: text/html; charset=utf-8');
        //echo __FILE__.'---'.__LINE__.'<br/>';
        //echo print_r($return, true);

        return $return;

        //$aeTask = \MarkNotes\Tasks\AddOrRename::getInstance();
        //$return = $aeTask->run(array('oldname' => $filename,'newname' => $newname,'type' => $type));
    }

    /**
     * Delete a file / folder
     */
    private static function delete(array &$params = array()) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $filename = $aeSession->get('filename');

        $type = $aeFunctions->getParam('type', 'string', '', false);

        $return = __FILE__.' - '.__LINE__.' - Kill '.$filename. ' type='.$type;

        //$aeTask = \MarkNotes\Tasks\Delete::getInstance();
        //$return = $aeTask->run(array('filename' => $filename,'type' => $type));

        return $return;
    }

    public static function run(&$params = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        self::cleanUp($params, $aeSettings->getFolderDocs(false));

        $task = $aeSession->get('task');
        $return = '';

        switch ($task) {
            case 'files.rename':
                $return = self::createRename($params);
                break;
            case 'files.delete':
                $return = self::delete($params);
                break;
        }

        header('Content-Type: application/json');
        echo $return;

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();

        // Only when the user is connected, we will not provide operating system functions
        // like create / delete / rename files/folders if the user isn't connected.

        if ($aeSession->get('authenticated', 0) === 1) {
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->bind('run.task', __CLASS__.'::run');
        }

        return true;
    }
}
