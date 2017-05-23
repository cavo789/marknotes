<?php

/**
 * Create / Rename or Delete a folder
 */

namespace MarkNotes\Plugins\Task\Files\Helpers;

defined('_MARKNOTES') or die('No direct access allowed');

class Folders
{
    protected static $hInstance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new Folders();
        }

        return self::$hInstance;
    }

    private static function is_dir_empty($foldername)
    {
        if (!is_readable($foldername)) {
            return null;
        }
        return (count(scandir($foldername)) == 2);
    }

    /**
     * Create a new folder
     */
    public static function createFolder(string $name) : float
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if (trim($name) === '') {
            return FILE_ERROR;
        }

        // Sanitize the foldername
        $name = utf8_decode($aeFiles->sanitizeFileName($name));
        $name = $aeSettings->getFolderDocs().$name;

        if ($aeFiles->folderExists($name)) {
            // The folder already exists
            return ALREADY_EXISTS;
        } elseif (!$aeFiles->folderExists(dirname($name))) {

            // The parent folder doesn't exists
            return FOLDER_NOT_FOUND;
        } else {
            if (!$aeFiles->folderExists($name)) {
                try {
                    mkdir($name, CHMOD_FOLDER);

                    return ($aeFiles->folderExists($name) ? CREATE_SUCCESS : FILE_ERROR);
                } catch (Exception $ex) {

                    /*<!-- build:debug -->*/
                    if ($aeSettings->getDebugMode()) {
                        $aeDebug->log($ex->getMessage(), 'error');
                    }
                    /*<!-- endbuild -->*/

                    return FILE_ERROR;
                } // try
            } // if (!$aeFiles->folderExists($name))
        } // if ($aeFiles->folderExists($name))
    }

    /**
     * Rename a folder
     */
    public static function renameFolder(string $oldname, string $newname) : float
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if ((trim($oldname) === '') || (trim($newname) === '')) {
            return FILE_ERROR;
        }

        // Sanitize foldersname
        $oldname = utf8_decode($aeFiles->sanitizeFileName($oldname));
        $oldname = $aeSettings->getFolderDocs().$oldname;

        $newname = utf8_decode($aeFiles->sanitizeFileName($newname));
        $newname = $aeSettings->getFolderDocs().$newname;

        if (!$aeFiles->folderExists($oldname)) {
            // The "old" folder is not found
            return FOLDER_NOT_FOUND;
        } else {
            if ($aeFiles->folderExists($newname)) {
                // The new folder already exists
                return ALREADY_EXISTS;
            } else {
                try {
                    rename($oldname, $newname);

                    return ($aeFiles->folderExists($newname) ? RENAME_SUCCESS : FILE_ERROR);
                } catch (Exception $ex) {

                    /*<!-- build:debug -->*/
                    if ($aeSettings->getDebugMode()) {
                        $aeDebug->log($ex->getMessage(), 'error');
                    }
                    /*<!-- endbuild -->*/

                    return FILE_ERROR;
                } // try
            } // if ($aeFiles->folderExists($newname))
        } // if (!$aeFiles->folderExists($oldname))
    }

    /**
     * Kill a folder recursively
     */
    public static function deleteFolder(string $foldername) : float
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if (trim($foldername) === '') {
            return FILE_ERROR;
        }

        // Sanitize foldersname
        $foldername = utf8_decode($aeFiles->sanitizeFileName($foldername));

        if (!$aeFiles->folderExists($foldername)) {
            // The "old" folder is not found
            return FOLDER_NOT_FOUND;
        } else {

            // $foldername will be something like c:\websites\notes\docs\folder\folder_to_kill
            // So be really sure that the $foldername absolute path is well within the $docs
            // folder (strcmp should strictly give 0).  if so, continue and allow the deletion
            // If not, stop and return an error.

            $docs = $aeSettings->getFolderDocs(true);

            if (strcmp($docs, substr($foldername, 0, strlen($docs))) !== 0) {
                // Outside the /docs folder, prohibited
                return FOLDER_NOT_DELETED;
            } elseif (!is_writable($foldername)) {
                // Don't start and kill files if the folder is read-only
                return FOLDER_IS_READONLY;
            } else {

                // Ok, recursively kill the folder and its content

                $it = new \RecursiveDirectoryIterator($foldername.DS, \RecursiveDirectoryIterator::SKIP_DOTS);

                $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

                foreach ($files as $file) {
                    $name = $file->getRealPath();

                    if ($file->isDir()) {

                        /*<!-- build:debug -->*/
                        if ($aeSettings->getDebugMode()) {
                            $aeDebug->log('Killing folder ['.utf8_encode($name).']', 'debug');
                        }
                        /*<!-- endbuild -->*/

                        if (is_writable($name)) {
                            try {
                                if (self::is_dir_empty($name)) {
                                    @rmdir($name);
                                } else {
                                    /*<!-- build:debug -->*/
                                    if ($aeSettings->getDebugMode()) {
                                        $aeDebug->log($name.' isn\'t empty', 'debug');
                                    }
                                    /*<!-- endbuild -->*/
                                }
                            } catch (Exception $ex) {
                                /*<!-- build:debug -->*/
                                if ($aeSettings->getDebugMode()) {
                                    $aeDebug->log($ex->getMessage(), 'error');
                                }
                                /*<!-- endbuild -->*/
                            }
                        }

                        if ($aeFiles->folderExists($name)) {
                            // Still exists

                            /*<!-- build:debug -->*/
                            if ($aeSettings->getDebugMode()) {
                                $aeDebug->log('   Error, folder ['.utf8_encode($name).'] still present', 'debug');
                            }
                            /*<!-- endbuild -->*/
                        }
                    } else { // if ($file->isDir())

                        /*<!-- build:debug -->*/
                        if ($aeSettings->getDebugMode()) {
                            $aeDebug->log('Killing file ['.utf8_encode($name).']', 'debug');
                        }
                        /*<!-- endbuild -->*/

                        if ((is_writable(dirname($name))) && (is_writable($name))) {
                            // Kill the file only if the file isn't readonly and the parent
                            // folder too
                            unlink($file->getRealPath());
                        }

                        if ($aeFiles->fileExists($name)) {
                            /*<!-- build:debug -->*/
                            if ($aeSettings->getDebugMode()) {
                                $aeDebug->log('   Error, file ['.utf8_encode($name).'] still present', 'debug');
                            }
                            /*<!-- endbuild -->*/
                        }
                    } // if ($file->isDir())
                } // foreach

                /*<!-- build:debug -->*/
                if ($aeSettings->getDebugMode()) {
                    $aeDebug->log('Killing file ['.utf8_encode($foldername).']', 'debug');
                }
                /*<!-- endbuild -->*/

                // And kill the folder itself
                try {
                    if (self::is_dir_empty($foldername)) {
                        rmdir($foldername);
                    }
                } catch (Exception $ex) {
                    /*<!-- build:debug -->*/
                    if ($aeSettings->getDebugMode()) {
                        $aeDebug->log($ex->getMessage(), 'error');
                    }
                    /*<!-- endbuild -->*/
                }

                if ($aeFiles->folderExists($foldername)) {
                    // Still exists

                    /*<!-- build:debug -->*/
                    if ($aeSettings->getDebugMode()) {
                        $aeDebug->log('   Error, folder ['.utf8_encode($foldername).'] still present', 'debug');
                    }
                    /*<!-- endbuild -->*/

                    return FILE_ERROR;
                } else { // if ($aeFiles->folderExists($foldername))
                    return KILL_SUCCESS;
                }
            }
        } // if (!$aeFiles->folderExists($foldername))
    }
}
