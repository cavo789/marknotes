<?php

/**
 * Create / Rename or Delete a file
 */

namespace MarkNotes\Plugins\Task\Files\Helpers;

defined('_MARKNOTES') or die('No direct access allowed');

class Files
{
    protected static $hInstance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new Files();
        }

        return self::$hInstance;
    }

    /**
     * Create a new note
     */
    public static function createFile(string $name) : float
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if (trim($name) === '') {
            return FILE_ERROR;
        }

        // Sanitize the filename
        $name = utf8_decode($aeFiles->sanitizeFileName($name));
        $name = $aeSettings->getFolderDocs().$name;

        if (!$aeFiles->fileExists($name)) {

            // Define the content : get the filename without the extension and set
            // the content as heading 1.  Don't use PHP_EOL but well PHP_LF

            $content = '# '.basename($aeFiles->removeExtension($name)).PHP_LF;

            return ($aeFiles->createFile($name, $content, CHMOD_FILE) ? CREATE_SUCCESS : FILE_ERROR);
        } else {

            // The file already exists
            return ALREADY_EXISTS;
        }
    }

    /**
     * Rename an existing note
     */
    public static function renameFile(string $oldname, string $newname) : float
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        if (trim($oldname) === '') {
            return FILE_ERROR;
        }

        // Sanitize filenames
        $oldname = $aeFiles->sanitizeFileName($oldname);
        $oldname = $aeSettings->getFolderDocs().$oldname;

        $newname = $aeFiles->sanitizeFileName($newname);
        $newname = $aeSettings->getFolderDocs().$newname;

        $wReturn = $aeFiles->renameFile($oldname, $newname);

        return ($wReturn ? RENAME_SUCCESS : FILE_ERROR);
    }

    /**
     * Kill a file
     */
    public function deleteFile(string $filename) : float
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();


        if (trim($filename) === '') {
            return FILE_ERROR;
        }
        if (!$aeFiles->fileExists($filename)) {
            return FILE_NOT_FOUND;
        } elseif (!is_writable(mb_convert_encoding($filename, "ISO-8859-1", "UTF-8"))) {
            return FILE_IS_READONLY;
        } else {
            try {
                unlink(mb_convert_encoding($filename, "ISO-8859-1", "UTF-8"));
                if (!$aeFiles->fileExists($filename)) {
                    return KILL_SUCCESS;
                }
            } catch (Exception $ex) {

                /*<!-- build:debug -->*/
                if ($aeSettings->getDebugMode()) {
                    $aeDebug = \MarkNotes\Debug::getInstance();
                    $aeDebug->log($ex->getMessage(), 'error');
                }
                /*<!-- endbuild -->*/
                return FILE_ERROR;
            } // try
        }
    }
}
