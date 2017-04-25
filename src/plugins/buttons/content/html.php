<?php

/**
 * Add a html export button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class HTML
{
    private static $ext = 'html';

    public static function add(&$buttons = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        $title = $aeSettings->getText('open_'.self::$ext, 'Open in a new window', true);

        $aeSession = \MarkNotes\Session::getInstance();
        $file = $aeSession->get('filename');
        $file = str_replace(DS, '/', $aeFiles->replaceExtension($file, self::$ext));

        $url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

        $buttons .=
            '<a id="icon_'.self::$ext.'" data-task="file" data-file="'.utf8_encode($url.$file).'" '.
                'title="'.$title.'" href="#">'.
                '<i class="fa fa-external-link" aria-hidden="true"></i>'.
              '</a>';

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('add.buttons', __CLASS__.'::add');
        return true;
    }
}
