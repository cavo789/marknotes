<?php

/**
 * Add a reveal export button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class Remark
{
    private static $ext = 'remark';

    public static function add(&$buttons = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        $title = 'remark | '.$aeSettings->getText('slideshow', 'Slideshow', true);

        $aeSession = \MarkNotes\Session::getInstance();
        $file = $aeSession->get('filename');
        $file = str_replace(DS, '/', $aeFiles->replaceExtension($file, self::$ext));

        // Get the default extension, as specified in the settings.json file
        $default = $aeSettings->getTask()['default'] ?? 'reveal';
        if ($default === self::$ext) {
            // The default extension is remark ==> no need to mention the extension
            $file = $aeFiles->removeExtension($file);
        }

        $url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

        $buttons .=
            '<a id="icon_'.self::$ext.'" data-task="file" data-file="'.utf8_encode($url.$file).'" '.
                'title="'.$title.'" href="#">'.
                '<i class="fa fa-desktop" aria-hidden="true"></i>'.
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
