<?php

namespace AeSecure\View;

include 'libs/autoload.php';

class Toolbar
{
    protected static $_instance = null;

    private $_aeSettings = null;

    public function __construct()
    {

        if (!class_exists('Settings')) {
            include_once dirname(__DIR__).DS.'settings.php';
        }

        $this->_aeSettings=\AeSecure\Settings::getInstance();

        return true;
    } // function __construct()

    public static function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new Toolbar();
        }

        return self::$_instance;
    } // function getInstance()

    /**
     * Return the toolbar
     *
     * @param  array  $params
     * @return {[type]       Nothing
     */
    public function getToolbar(array $params = null)
    {

        // Retrieve the URL to this note
        $thisNote= urldecode(\AeSecure\Functions::getCurrentURL(false, false));

        $url=rtrim(\AeSecure\Functions::getCurrentURL(false, false), '/').'/'.rtrim($this->_aeSettings->getFolderDocs(false), DIRECTORY_SEPARATOR).'/';
        $urlHTML=$url.str_replace(DIRECTORY_SEPARATOR, '/', \AeSecure\Files::replaceExtension($params['filename'], 'html'));

        $icons=
          '<a title="'.$this->_aeSettings->getText('fullscreen', 'Display the note in fullscreen', true).'" href="#">'.
            '<i id="icon_fullscreen" data-task="fullscreen" class="fa fa-arrows-alt" aria-hidden="true"></i>'.
          '</a>'.
          '<a title="'.$this->_aeSettings->getText('timeline', 'Display notes in a timeline view', true).'" href="#">'.
            '<i id="icon_timeline" data-task="timeline" class="fa fa-calendar" aria-hidden="true"></i>'.
          '</a>'.
          '<a title="'.$this->_aeSettings->getText('refresh', 'Refresh', true).'" href="#">'.
            '<i id="icon_refresh" data-task="display" data-file="'.$params['filename'].'" class="fa fa-refresh" aria-hidden="true"></i>'.
          '</a>'.
          '<a title="'.$this->_aeSettings->getText('copy_clipboard', 'Copy the note&#39;s content, with page layout, in the clipboard', true).'" href="#">'.
            '<i id="icon_clipboard" data-task="clipboard" class="fa fa-clipboard" data-clipboard-target="#note_content" aria-hidden="true"></i>'.
          '</a>'.
          '<a title="'.$this->_aeSettings->getText('print_preview', 'Print preview', true).'" href="#">'.
            '<i id="icon_printer" data-task="printer" class="fa fa-print" aria-hidden="true"></i>'.
          '</a>'.
          '<a h title="'.$this->_aeSettings->getText('export_pdf', 'Export the note as a PDF document', true).'" ref="#">'.
            '<i id="icon_pdf" data-task="pdf" data-file="'.utf8_encode($urlHTML).'?format=pdf" class="fa fa-file-pdf-o" aria-hidden="true"></i>'.
          '</a>'.
          '<a title="'.$this->_aeSettings->getText('copy_link', 'Copy the link to this note in the clipboard', true).'" href="#">'.
            '<i id="icon_link_note" data-task="link_note" class="fa fa-link" data-clipboard-text="'.$thisNote.'" aria-hidden="true"></i>'.
          '</a>'.
          '<a title="'.$this->_aeSettings->getText('slideshow', 'slideshow', true).'" href="#">'.
            '<i id="icon_slideshow" data-task="slideshow" data-file="'.utf8_encode($urlHTML).'?format=slides" class="fa fa-desktop" aria-hidden="true"></i>'.
          '</a>'.
          '<a title="'.$this->_aeSettings->getText('open_html', 'Open in a new window').'" href="#">'.
            '<i id="icon_window" data-task="window" data-file="'.utf8_encode($urlHTML).'" class="fa fa-external-link" aria-hidden="true"></i>'.
          '</a>'.
          (
              $this->_aeSettings->getEditAllowed()
              ?'<a title="'.$this->_aeSettings->getText('edit_file', 'Edit').'" data-file="'.$params['filename'].'" href="#">'.
                  '<i id="icon_edit" data-task="edit" class="fa fa-pencil-square-o" aria-hidden="true"></i>'.
                '</a>'
              :''
          ).
          '<a title="'.$this->_aeSettings->getText('settings_clean', 'Clear cache', true).'" href="#">'.
            '<i id="icon_settings_clear" data-task="clear" class="fa fa-eraser" aria-hidden="true"></i>'.
          '</a>';

        $toolbar='<div id="icons" class="onlyscreen fa-1x">'.$icons.'</div>';

        $toolbar='<div id="toolbar-button" data-toolbar="style-option" class="onlyscreen btn-toolbar btn-toolbar-default"><i class="fa fa-cog"></i></div>';
        $toolbar.='<div id="toolbar-options" class="hidden btn-toolbar-warning">'.$icons.'</div>';

        // Attach the JS code to the toolbar
        $toolbar.='<script>'.
          'if ($.isFunction($.fn.toolbar)) {'.
            '$(\'#toolbar-button\').toolbar({'.
              'content: \'#toolbar-options\','.
              'position: \'bottom\','.
              'style: \'default\''.
            '});'.
          '}'.
          '</script>';

        return $toolbar;
    }  // function getToolbar()
} // class Toolbar
