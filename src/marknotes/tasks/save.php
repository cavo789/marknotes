<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

/**
* Save the new content of the file.   This function is called by the "Save" button available in the JS editor
*/

class Save
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Save();
        }

        return self::$_instance;
    }

    public function run(array $params)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $return = array();

        if (!$aeSettings->getEditAllowed()) {
            $return = array('status' => array('success' => 0,'message' => $aeSettings->getText('no_save_allowed', 'Error, saving notes isn&#39;t allowed')));
        } else { // if (!$aeSettings->getEditAllowed())

            // If the filename doesn't mention the file's extension, add it.
            if (substr($params['filename'], -3) != '.md') {
                $params['filename'] .= '.md';
            }

            $fullname = str_replace('/', DIRECTORY_SEPARATOR, $aeSettings->getFolderDocs(true).utf8_decode(ltrim($params['filename'], DS)));

            // Call content plugins
            $markdown = $params['markdown'];
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->loadPlugins('markdown');
            $args = array(&$markdown);
            $aeEvents->trigger('markdown.write', $args);
            $params['markdown'] = $args[0];

            $status = array('success' => 1,'message' => $aeSettings->getText('button_save_done', 'The file has been successfully saved'));

            $return['status'] = $status;
            $return['filename'] = $fullname;
        } // if (!$aeSettings->getEditAllowed())

        return json_encode($return, JSON_PRETTY_PRINT);
    }
}
