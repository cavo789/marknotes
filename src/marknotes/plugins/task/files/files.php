<?php

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Files
{

    /**
     * Be sure that filenames doesn't already start with the /docs folder (otherwise will
     * be mentionned twice)
     */
    private static function cleanUp() : bool
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
        return $aeJSON->json_encode($arr);
    }

    /**
     * Create a folder on the disk
     */
    private static function createFolder(string $foldername) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFilesystem = \MarkNotes\Plugins\Task\Files\Helpers\Folders::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

        $foldername = $aeFiles->sanitizeFileName($foldername);

        // Try to create a file called "$filename.md" on the disk
        $wReturn = $aeFilesystem->createFolder($foldername);

        switch ($wReturn) {
            case CREATE_SUCCESS:
                $msg = str_replace(
                    '%s',
                    str_replace($aeSettings->getFolderDocs(true), '', utf8_encode($foldername)),
                    $aeSettings->getText('folder_created', 'The folder [%s] has been created on the disk')
                );

                break;
            case FOLDER_NOT_FOUND:

                // The parent folder seems to be missing (renamed outside marknotes?)

                $msg = str_replace(
                    '%s',
                    str_replace($aeSettings->getFolderDocs(true), '', dirname($foldername)),
                    $aeSettings->getText('folder_not_found', 'The folder [%s] doesn\\&#39;t exists')
                );

                break;
            case ALREADY_EXISTS:
                $msg = str_replace(
                    '%s',
                    str_replace($aeSettings->getFolderDocs(true), '', utf8_encode($foldername)),
                    $aeSettings->getText('file_already_exists')
                );

                break;
            default:
                $msg = str_replace(
                    '%s',
                    str_replace($aeSettings->getFolderDocs(true), '', utf8_encode($foldername)),
                    $aeSettings->getText('error_create_folder')
                );

                break;
        }

        $arr = array(
            'status' => (($wReturn == CREATE_SUCCESS) ? 1 : 0),
            'action' => 'create',
            'type' => 'folder',
			'md5' => md5($docs.$foldername),
            'msg' => $msg,
            'foldername' => utf8_encode($foldername)
        );

        return self::returnInfo($arr);
    }

    /**
     * Rename a folder on the disk
     */
    private static function renameFolder(string $oldname, string $newname) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFilesystem = \MarkNotes\Plugins\Task\Files\Helpers\Folders::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

        $oldname = $aeFiles->sanitizeFileName($oldname);
        $newname = $aeFiles->sanitizeFileName($newname);

        $wReturn = $aeFilesystem->renameFolder($oldname, $newname);

        switch ($wReturn) {
            case RENAME_SUCCESS:

                $msg = sprintf(
                    $aeSettings->getText('folder_renamed', 'The folder [%s] has been renamed into [%s]'),
                    str_replace($aeSettings->getFolderDocs(true), '', $oldname),
                    str_replace($aeSettings->getFolderDocs(true), '', $newname)
                );

                break;
            case FOLDER_NOT_FOUND:

                $msg = str_replace(
                    '%s',
                    str_replace($aeSettings->getFolderDocs(true), '', $oldname),
                    $aeSettings->getText('folder_not_found', 'The folder [%s] doesn\\&#39;t exists')
                );

                break;
            default:

                $msg = sprintf(
                    $aeSettings->getText('error_rename_folder', 'An error has occured when trying to rename the folder [%s] into [%s]'),
                    str_replace($aeSettings->getFolderDocs(true), '', $oldname),
                    str_replace($aeSettings->getFolderDocs(true), '', $newname)
                );

                break;
        }

        $arr = array(
            'status' => (($wReturn == RENAME_SUCCESS) ? 1 : 0),
            'action' => 'rename',
            'type' => 'folder',
			'md5' => md5($docs.$newname),
            'msg' => $msg,
            'foldername' => utf8_encode($newname)
        );

        return self::returnInfo($arr);
    }

    /**
     * Kill a folder on the disk
     */
    private static function deleteFolder(string $foldername) : string
    {
        $aeFilesystem = \MarkNotes\Plugins\Task\Files\Helpers\Folders::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

        $wReturn = $aeFilesystem->deleteFolder($foldername);

        switch ($wReturn) {
            case KILL_SUCCESS:

                $msg = sprintf($aeSettings->getText('folder_deleted',
                    'The folder [%s] and its content has been deleted'),
                    str_replace($aeSettings->getFolderDocs(true), '', $foldername));
                break;
            case FOLDER_NOT_DELETED:

                $msg = sprintf($aeSettings->getText('folder_not_deleted',
                    'The folder [%s] is outside your documentation root folder '.
                    'and therefore will not be deleted'), $foldername);
                break;
            case FOLDER_NOT_FOUND:

                $msg = sprintf($aeSettings->getText('folder_not_found',
                    'The folder [%s] doesn\\&#39;t exists'),
                    str_replace($aeSettings->getFolderDocs(true), '', $foldername));

                break;
            case FOLDER_IS_READONLY:

                $msg = sprintf($aeSettings->getText('folder_read_only', 'Sorry but '.
                    'the folder [%s] is read-only'),
                    str_replace($aeSettings->getFolderDocs(true), '', $foldername));
                break;
            default:
                $msg = str_replace(
                    '%s',
                    str_replace($aeSettings->getFolderDocs(true), '', $foldername),
                    $aeSettings->getText('error_delete_folder', 'An error has occured during the deletion of the folder [%s] (this is the case when the folder contains readonly subfolders or notes)')
                );
                break;
        }

        $arr = array(
            'status' => (($wReturn == KILL_SUCCESS) ? 1 : 0),
            'action' => 'delete',
            'type' => 'folder',
			'md5' => md5($docs.$foldername),
            'msg' => $msg,
            'foldername' => utf8_encode($foldername)
        );

        return self::returnInfo($arr);
    }

    /**
     * Create a note on the disk
     */
    private static function createFile(string $filename) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFilesystem = \MarkNotes\Plugins\Task\Files\Helpers\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

        // Be sure to have the .md extension
        $filename = $aeFiles->removeExtension($filename).'.md';

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
            str_replace($aeSettings->getFolderDocs(true), '', utf8_encode($filename)),
            $aeSettings->getText($code)
        );

        $arr = array(
            'status' => (($wReturn === CREATE_SUCCESS) ? 1 : 0),
            'action' => 'create',
            'type' => 'file',
            'msg' => $msg,
			'md5' => md5($docs.$filename),
            'filename' => utf8_encode($filename)
        );

        return self::returnInfo($arr);
    }

    /**
     * Rename a note on the disk
     */
    private static function renameFile(string $oldname, string $newname) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFilesystem = \MarkNotes\Plugins\Task\Files\Helpers\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

        // Be sure to have the .md extension
        $oldname = $aeFiles->removeExtension($oldname).'.md';
        $newname = $aeFiles->removeExtension($newname).'.md';

        // Try to create a file called "$filename.md" on the disk
        $wReturn = $aeFilesystem->renameFile($oldname, $newname);

        switch ($wReturn) {
            case RENAME_SUCCESS:
                $msg = sprintf(
                    $aeSettings->getText('file_renamed', 'The note [%s] has been renamed into [%s]'),
                    str_replace($aeSettings->getFolderDocs(true), '', $oldname),
                    str_replace($aeSettings->getFolderDocs(true), '', $newname)
                );

                break;
            default:

                $msg = sprintf(
                    $aeSettings->getText('error_rename_file', 'An error has occured when trying to rename the note [%s] into [%s]'),
                    str_replace($aeSettings->getFolderDocs(true), '', $oldname),
                    str_replace($aeSettings->getFolderDocs(true), '', $newname)
                );

                break;
        }

        $arr = array(
            'status' => (($wReturn == RENAME_SUCCESS) ? 1 : 0),
            'action' => 'rename',
            'type' => 'file',
            'msg' => $msg,
			'md5' => md5($docs.$newname),
            'filename' => utf8_encode($newname)
        );

        return self::returnInfo($arr);
    }

    /**
     * Kill a note on the disk
     */
    private static function deleteFile(string $filename) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFilesystem = \MarkNotes\Plugins\Task\Files\Helpers\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		$docs = str_replace('/', DS, $aeSettings->getFolderDocs(false));

        // Be sure to have the .md extension
        $filename = $aeFiles->removeExtension($filename).'.md';
        // Try to create a file called "$filename.md" on the disk
        $wReturn = $aeFilesystem->deleteFile($filename);

        switch ($wReturn) {
            case KILL_SUCCESS:
                $msg = sprintf($aeSettings->getText('file_deleted', 'The note [%s] has '.
                    'been successfully deleted'), $filename);

                break;
            case FILE_NOT_FOUND:
                $msg = sprintf($aeSettings->getText('file_not_found', 'The note [%s] doesn\\&#39;t exists'), $filename);

                break;
            case FILE_IS_READONLY:
                $msg = sprintf($aeSettings->getText('file_read_only', 'The note [%s] is read-only, it\\&#39;s then impossible to delete it'), $filename);

                break;
            default:
                $msg = sprintf($aeSettings->getText('error_delete_file', 'An error has occured during the deletion of the note [%s]'), $filename);
                break;
        }

        $arr = array(
            'status' => (($wReturn == KILL_SUCCESS) ? 1 : 0),
            'action' => 'delete',
            'type' => 'file',
            'msg' => $msg,
			'md5' => md5($docs.$filename),
            'filename' => utf8_encode($filename)
        );

        return self::returnInfo($arr);
    }

    /**
     * Create a file / folder
     */
    private static function create() : string
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // file or folder
        $type = $aeFunctions->getParam('type', 'string', '', false);

		// Get the file name and remove dangerous characters
        $newname = trim(urldecode($aeFunctions->getParam('param', 'string', '', true)));

        if ($newname != '') {
            $newname = $aeFiles->sanitizeFileName(trim($newname));
        }

        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            $aeDebug->log(__METHOD__, 'debug');
            $aeDebug->log('Newname=['.$newname.']', 'debug');
            $aeDebug->log('Type=['.$type.']', 'debug');
        }
        /*<!-- endbuild -->*/

        if ((trim($type) === '') || (trim($newname) === '')) {
            $return = array(
                'status' => 0,
                'action' => 'create',
                'type' => $type,
                'msg' => $aeSettings->getText('unknown_error', 'An error has occured, please try again')
            );
        } else {
            if ($type === 'folder') {

                // it's a folder
                $return = self::createFolder($newname);
            } else {
                // it's a file
                $return = self::createFile($newname);
            }
        }

        return $return;
    }

    /**
     * Rename a file / folder
     */
    private static function rename() : string
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // file or folder
        $type = $aeFunctions->getParam('type', 'string', '', false);

        $newname = trim(urldecode($aeFunctions->getParam('param', 'string', '', true)));
        if ($newname != '') {
            $newname = $aeFiles->sanitizeFileName(trim($newname));
        }

        $oldname = trim(urldecode($aeFunctions->getParam('oldname', 'string', '', true)));

        if ($oldname != '') {
            $oldname = $aeFiles->sanitizeFileName(trim($oldname));
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
            $return = array(
                'status' => 0,
                'action' => 'rename',
                'type' => $type,
                'msg' => $aeSettings->getText('unknown_error', 'An error has occured, please try again')
            );
        } else {
            if ($type === 'folder') {

                // it's a folder
                $return = self::renameFolder($oldname, $newname);
            } else {

                // it's a file
                $return = self::renameFile($oldname, $newname);
            }
        }

        return $return;
    }

    /**
     * Delete a file / folder
     */
    private static function delete() : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // file or folder
        $type = $aeFunctions->getParam('type', 'string', '', false);

        $name = trim(urldecode($aeFunctions->getParam('param', 'string', '', true)));
        $name = $aeFiles->sanitizeFileName($name);
        if ($name === '') {
            $return = array(
                'status' => 0,
                'action' => 'delete',
                'type' => $type,
                'msg' => $aeSettings->getText('unknown_error', 'An error has occured, please try again'),
                'filename' => $name
            );
        } else {

            // Get the fullname of the folder/file name
            $name = $aeSettings->getFolderDocs(true).$name;
            $name = $aeFiles->sanitizeFileName($name);


            if ($type === 'folder') {

                // it's a folder
                $return = self::deleteFolder($name);
            } else {
                // it's a file
                $return = self::deleteFile($name);
            }
        } // if ($name === '')

        return $return;
    }

    public static function run(array &$params = null)
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		// Be sure that filenames doesn't already start with the /docs folder
        self::cleanUp($params, $aeSettings->getFolderDocs(false));

        $task = trim($aeSession->get('task'));
        $return = '';

        switch ($task) {

            case 'files.create':
                // Add a new file/folder
                $return = self::create();
                break;

            case 'files.rename':
                // Rename an existing one
                $return = self::rename();
                break;

            case 'files.delete':
                // Remove an existing file/folder
                $return = self::delete();
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
