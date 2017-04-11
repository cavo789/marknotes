<?php

namespace MarkNotes\View;

defined('_MARKNOTES') or die('No direct access allowed');

include 'libs/autoload.php';

class Toolbar
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Toolbar();
        }

        return self::$_instance;
    }

    /**
     * Return the toolbar
     *
     * @param  array  $params
     * @return {[type]       Nothing
     */
    public function getToolbar(array $params = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // Retrieve the URL to this note
        $thisNote = 'index.php?task=display&param='.$_REQUEST['param'];

        $url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
        $urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($params['filename'], 'html'));

        $icons =
          '<a id="icon_fullscreen" data-task="fullscreen" title="'.$aeSettings->getText('fullscreen', 'Display the note in fullscreen', true).'" href="#">'.
            '<i class="fa fa-arrows-alt" aria-hidden="true"></i>'.
          '</a>'.
          '<a id="icon_refresh" data-task="display" data-file="'.$params['filename'].'" title="'.$aeSettings->getText('refresh', 'Refresh', true).'" href="#">'.
            '<i class="fa fa-refresh" aria-hidden="true"></i>'.
          '</a>'.
          '<a id="icon_clipboard" data-task="clipboard" data-clipboard-target="#note_content" title="'.$aeSettings->getText('copy_clipboard', 'Copy the note&#39;s content, with page layout, in the clipboard', true).'" href="#">'.
            '<i class="fa fa-clipboard"aria-hidden="true"></i>'.
          '</a>'.
          '<a id="icon_printer" data-task="printer" title="'.$aeSettings->getText('print_preview', 'Print preview', true).'" href="#">'.
            '<i class="fa fa-print" aria-hidden="true"></i>'.
          '</a>'.
          '<a id="icon_pdf" data-task="pdf" data-file="'.utf8_encode($urlHTML).'?format=pdf" title="'.$aeSettings->getText('export_pdf', 'Export the note as a PDF document', true).'" href="#">'.
            '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>'.
          '</a>'.
          '<a id="icon_link_note" data-task="link_note" data-clipboard-text="'.$thisNote.'" title="'.$aeSettings->getText('copy_link', 'Copy the link to this note in the clipboard', true).'" href="#">'.
            '<i class="fa fa-link" aria-hidden="true"></i>'.
          '</a>'.
          '<a id="icon_slideshow" data-task="slideshow" data-file="'.utf8_encode($urlHTML).'?format=slides" title="'.$aeSettings->getText('slideshow', 'slideshow', true).'" href="#">'.
            '<i class="fa fa-desktop" aria-hidden="true"></i>'.
          '</a>'.
          '<a id="icon_window" data-task="window" data-file="'.utf8_encode($urlHTML).'" title="'.$aeSettings->getText('open_html', 'Open in a new window').'" href="#">'.
            '<i class="fa fa-external-link" aria-hidden="true"></i>'.
          '</a>'.
          (
              $aeSettings->getEditAllowed()
              ?'<a id="icon_edit" data-task="edit" title="'.$aeSettings->getText('edit_file', 'Edit').'" data-file="'.$params['filename'].'" href="#">'.
                  '<i class="fa fa-pencil-square-o" aria-hidden="true"></i>'.
                '</a>'
              :''
          );

        $toolbar = '<div id="icons" class="onlyscreen fa-1x">'.$icons.'</div>';

        $toolbar = '<div id="toolbar-button" data-toolbar="style-option" class="onlyscreen btn-toolbar btn-toolbar-default"><i class="fa fa-cog"></i></div>';
        $toolbar .= '<div id="toolbar-options" class="hidden btn-toolbar-warning">'.$icons.'</div>';

        // Attach the JS code to the toolbar (see https://github.com/paulkinzett/toolbar)
        // @TODO : Should be put in marknotes.js, in the afterDisplay() function but doesn't work, don't know why
        $toolbar .= '<script>'.
          'if ($.isFunction($.fn.toolbar)) {'.
            '$(\'#toolbar-button\').toolbar({'.
              'content: \'#toolbar-options\','.
              'position: \'bottom\','.
              'style: \'default\','.
              'event: \'click\','.
              'hideOnClick: true'.
            '});'.
          '}'.
          '</script>';

        return $toolbar;
    }
}
