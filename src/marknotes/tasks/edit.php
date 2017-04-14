<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class Edit
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    } // function __construct()

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Edit();
        }

        return self::$_instance;
    } // function getInstance()

    private static function doIt(array $params) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        header('Content-Type: text/plain; charset=utf-8');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3) != '.md') {
            $params['filename'] .= '.md';
        }

        $fullname = str_replace('/', DS, ($aeSettings->getFolderDocs(true).ltrim($params['filename'], DS)));

        if (!$aeFiles->fileExists($fullname)) {
            echo str_replace('%s', '<strong>'.$fullname.'</strong>', $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists'));
            die();
        }

        $params['editMode']=1;

        $aeMD = \MarkNotes\FileType\Markdown::getInstance();
        $markdown = $aeMD->read($fullname, $params);

        $sReturn = '<div class="editor-wrapper"><strong class="filename">'.utf8_encode($fullname).'</strong><textarea id="sourceMarkDown">'.$markdown.'</textarea></div>';

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
            $sReturn = $aeFunctions->showError('not_authenticated', 'You need first to authenticate', true);
        }

        return $sReturn;
    }
}
