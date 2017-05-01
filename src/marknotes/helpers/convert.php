<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes\Helpers;

defined('_MARKNOTES') or die('No direct access allowed');

class Convert
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Convert();
        }

        return self::$_instance;
    }

    /**
     *  Convert the Markdown string into a HTML one
     */
    public function getHTML(string $markdown, array $params = null) : string
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // Call the Markdown parser (https://github.com/erusev/parsedown)
        $lib = $aeSettings->getFolderLibs()."parsedown/Parsedown.php";
        if (!file_exists($lib)) {
            self::ShowError(
                str_replace(
                    '%s',
                    '<strong>'.$lib.'</strong>',
                    $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists')
                ),
                true
            );
        }

        // When the task isn't slides (or reveal/remark), the --- (or -----) shouldn't be taken into consideration
        // Indeed, --- (or ------) are used in slideshows to create a new slide so ignore these characters

        $task = $params['task'] ?? '';
        if (!in_array($task, array('reveal','remark','slides'))) {
            $markdown = preg_replace('/('.NEW_SLIDE.')/m', '', $markdown);
        }

        include_once $lib;
        $parsedown = new \Parsedown();
        
        return $parsedown->text(trim($markdown));
    }
}
