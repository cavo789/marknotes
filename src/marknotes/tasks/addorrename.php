<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

/**
 * Create file/folder or rename file/folder
 */

class AddOrRename
{
    protected static $hInstance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new AddOrRename();
        }

        return self::$hInstance;
    }
/*
    private static function createFile(array &$params) : bool
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $params['newname'] = $aeFiles->removeExtension($aeFiles->sanitizeFileName($params['newname'])).'.md';

        // Define the content : get the filename without the extension and set the content as heading 1
        $content = '# '.basename($aeFiles->removeExtension($params['newname'])).PHP_EOL;

        return $aeFiles->createFile($params['newname'], $content, $aeSettings->getchmod('file'));
    }*/
/*
    private static function cleanUp(array &$params)
    {

        // Be sure that filenames doesn't already start with the /docs folder (otherwise will
        // be mentionned twice)

        $aeSettings = \MarkNotes\Settings::getInstance();

        $docs = $aeSettings->getFolderDocs(false);

        if (substr($params['newname'], 0, strlen($docs)) == $docs) {
            $params['newname'] = substr($params['newname'], strlen($docs));
        }
        if (substr($params['oldname'], 0, strlen($docs)) == $docs) {
            $params['oldname'] = substr($params['oldname'], strlen($docs));
        }

        $params['oldname'] = $aeSettings->getFolderDocs(true).$params['oldname'];
        $params['newname'] = $aeSettings->getFolderDocs(true).$params['newname'];
    }
*/
    private static function doIt(array $params) : string
    {
        /*
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeJSON = \MarkNotes\JSON::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        self::cleanUp($params, $aeSettings->getFolderDocs(false));

        if ($aeSettings->getDebugMode()) {
            $aeDebug->log(__METHOD__, 'debug');
            $aeDebug->log('Oldname '.utf8_encode($params['oldname']), 'debug');
            $aeDebug->log('Newname '.utf8_encode($params['newname']), 'debug');
            $aeDebug->log('Type '.$params['type'], 'debug');
        }*/

        $sReturn = '';

        try {

            /*
            $old = $params['oldname'];
            if (mb_detect_encoding($old)) {
                if (!file_exists($old)) {
                    $old = utf8_decode($old);
                }
            }
       	*/

            // use utf8_decode since the name can contains accentuated characters
            if (!is_writable(dirname($old))) {
                $aeDebug->log('The folder ['. dirname($old). '] is read-only', 'error');

                // The folder is read-only, we can't add new folder / notes
                $msg = str_replace(
                    '%s',
                    utf8_encode(dirname($params['oldname'])),
                    $aeSettings->getText('folder_read_only', 'Sorry but the folder [%s] is read-only, no new folder or note can&#39;t be added there')
                );
                $sReturn = $aeJSON->json_return_info(array('status' => 0,'msg' => $msg), array());
            }
        } catch (Exception $ex) {
            $sReturn = $aeJSON->json_return_info(array('status' => 0,'msg' => utf8_encode($ex->getMessage())), array());
        } // try

        if ($params['type'] === 'folder') {

             // Operation on a folder : create or rename

            if (!$aeFiles->folderExists(utf8_decode($params['oldname']))) {
                // create a new folder (oldname is perhaps "c:\sites\notes\docs\new folder" while
                // newname can be "c:\sites\notes\docs\developments" i.e. the name given by the user in the
                // frontend interface

                try {
                    mkdir(utf8_decode($params['newname']), CHMOD_FOLDER);

                    if ($aeFiles->folderExists(utf8_decode($params['newname']))) {
                        $msg = str_replace(
                            '%s',
                            str_replace($aeSettings->getFolderDocs(true), '', $params['newname']),
                            $aeSettings->getText('folder_created', 'The folder [%s] has been created on the disk')
                        );

                        $sReturn = $aeJSON->json_return_info(
                            array(
                                'status' => 1,
                                'action' => 'create',
                                'type' => $params['type'],
                                'msg' => $msg
                            ),
                            array()
                        );
                    } else { // if (is_dir($params['newname']))

                        $msg = str_replace(
                            '%s',
                            $params['newname'],
                            $aeSettings->getText('error_create_folder', 'An error has occured during the creation of the folder [%s]')
                        );

                        $sReturn = $aeJSON->json_return_info(
                            array(
                                'status' => 0,
                                'action' => 'create',
                                'type' => $params['type'],
                                'msg' => $msg
                            ),
                            array()
                        );
                    } // if (is_dir($params['newname']))
                } catch (Exception $ex) {
                    $sReturn = $aeJSON->json_return_info(
                        array(
                            'status' => 0,
                            'action' => 'create',
                            'type' => $params['type'],
                            'msg' => $ex->getMessage()
                        ),
                        array()
                    );
                } // try
            } elseif ($aeFiles->folderExists(utf8_decode($params['oldname']))) {
                // Rename an existing folder

                rename(utf8_decode($params['oldname']), utf8_decode($params['newname']));

                if ($aeFiles->folderExists(utf8_decode($params['newname']))) {
                    $msg = sprintf(
                        $aeSettings->getText('folder_renamed', 'The folder [%s] has been renamed into [%s]'),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['oldname']),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['newname'])
                    );

                    $sReturn = $aeJSON->json_return_info(
                        array(
                            'status' => 1,
                            'action' => 'rename',
                            'type' => $params['type'],
                            'msg' => $msg
                        ),
                        array()
                    );
                } else { // if (is_dir($params['newname']))

                    $msg = sprintf(
                        $aeSettings->getText('error_rename_folder', 'An error has occured when trying to rename the folder [%s] into [%s]'),
                        str_replace($params['folder'], '', $params['oldname']),
                        str_replace($params['folder'], '', $params['newname'])
                    );

                    $sReturn = $aeJSON->json_return_info(
                        array(
                            'status' => 0,
                            'action' => 'rename',
                            'type' => $params['type'],
                            'msg' => $msg
                        ),
                        array()
                    );
                } // if (is_dir($params['newname']))
            }
        } else { // if ($params['type']==='folder')

            // Operation on a file : create or rename

            $aeDebug->log(__METHOD__, true);

            // It's a file, be sure to have the .md extension
            $params['oldname'] = $aeFiles->removeExtension($params['oldname']).'.md';
            $params['newname'] = $aeFiles->removeExtension($params['newname']).'.md';

            if (!$aeFiles->fileExists(utf8_decode($params['oldname']))) {
                self::createFile($params);
                // Define the filename

                if ($aeFiles->fileExists(utf8_decode($params['newname']))) {
                    $msg = str_replace(
                        '%s',
                        str_replace($aeSettings->getFolderDocs(true), '', $params['newname']),
                        $aeSettings->getText('file_created', 'The note [%s] has been successfully created on the disk')
                    );

                    $sReturn = $aeJSON->json_return_info(
                        array(
                            'status' => 1,
                            'action' => 'create',
                            'type' => $params['type'],
                            'msg' => $msg,
                            'filename' => str_replace($aeSettings->getFolderDocs(true), '', $params['newname'])
                        ),
                        array()
                    );
                } else { // if ($aeFiles->fileExists($params['newname']))

                    $msg = str_replace(
                        '%s',
                        $params['newname'],
                        $aeSettings->getText('error_create_file', 'An error has occured during the creation of the note [%s]')
                    );

                    $sReturn = $aeJSON->json_return_info(
                        array(
                            'status' => 0,
                            'action' => 'create',
                            'type' => $params['type'],
                            'msg' => $msg
                        ),
                        array()
                    );
                } // if ($aeFiles->fileExists($params['newname']))
            } elseif ($aeFiles->fileExists(utf8_decode($params['oldname']))) {
                rename(utf8_decode($params['oldname']), utf8_decode($params['newname']));

                if ($aeFiles->fileExists(utf8_decode($params['newname']))) {
                    $msg = sprintf(
                        $aeSettings->getText('file_renamed', 'The note [%s] has been renamed into [%s]'),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['oldname']),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['newname'])
                    );

                    $sReturn = $aeJSON->json_return_info(
                        array(
                            'status' => 1,
                            'action' => 'rename',
                            'type' => $params['type'],
                            'msg' => $msg
                        ),
                        array()
                    );
                } else { // if (is_dir($params['newname']))

                    $msg = sprintf(
                        $aeSettings->getText('error_rename_file', 'An error has occured when trying to rename the note [%s] into [%s]'),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['oldname']),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['newname'])
                    );

                    $sReturn = $aeJSON->json_return_info(
                        array(
                            'status' => 0,
                            'action' => 'rename',
                            'type' => $params['type'],
                            'msg' => $msg
                        ),
                        array()
                    );
                } // if (is_dir($params['newname']))
            }
        } // if ($params['type']==='folder')

        return $sReturn;
    }

    public static function run(array $params)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        // Only when the user is connected
        if ($aeSession->get('authenticated', 0) === 1) {
            $sReturn = self::doIt($params);
        } else {
            $arr = array('status' => 0,'msg' => $aeFunctions->showError('not_authenticated', 'You need first to authenticate', false));
            $sReturn = json_encode($arr);
        }

        return $sReturn;
    }
}
