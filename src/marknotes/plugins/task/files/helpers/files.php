<?php

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
            // the content as heading 1
            $content = '# '.basename($aeFiles->removeExtension($name)).PHP_EOL;

            return ($aeFiles->createFile($name, $content, CHMOD_FILE) ? CREATE_SUCCESS : FILE_ERROR);
        } else {

            // The file already exists
            return ALREADY_EXISTS;
        }
    }
}
