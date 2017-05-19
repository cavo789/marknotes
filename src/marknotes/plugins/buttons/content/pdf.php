<?php

/**
 * Add a pdf export button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class PDF
{
    private static $ext = 'pdf';

    public static function add(&$buttons = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        $title = $aeSettings->getText('export_'.self::$ext, 'Export the note as a PDF document', true);

        $aeSession = \MarkNotes\Session::getInstance();
        $file = $aeSession->get('filename');
        $file = str_replace(DS, '/', $aeFiles->replaceExtension($file, self::$ext));

        // Get the default extension, as specified in the settings.json file
        $default = $aeSettings->getTask()['default'] ?? 'reveal';
        if ($default === self::$ext) {
            // The default extension is pdf ==> no need to mention the extension
            $file = $aeFiles->removeExtension($file);
        }

        $url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

        $buttons .=
            '<a id="icon_'.self::$ext.'" data-task="file" data-file="'.utf8_encode($url.$file).'" '.
                'title="'.$title.'" href="#">'.
                '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>'.
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
