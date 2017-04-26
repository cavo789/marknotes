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

        // When the task isn't slideshow, the --- (or -----) shouldn't be considered as an horizontal break (<hr>).
        // --- (or ------) is used in slideshows to create a new slide so, before converting the markdown note in html
        // thanks for Parsedown, remove them from the source

        if (isset($params['task'])) {
            if ($params['task'] !== 'slideshow') {
                // A manual section break (i.e. a new slide) can be manually created in marknotes by just creating, in the
                // note a new line with --- (or -----).  Only these characters on the beginning of the line.
                $newSlide = '\n+^-{3,5}$\n+';

                // Except when outputting as a slideshow, remove the --- (or -----) if these characters are preceded and
                // followed by an empty line and --- (or -----) are the only characters on the line
                // (==> so it's a "section break")
                $markdown = preg_replace('/('.$newSlide.')/m', '', $markdown);
            }
        }
        include_once $lib;
        $parsedown = new \Parsedown();

        return $parsedown->text(trim($markdown));
    }
}
