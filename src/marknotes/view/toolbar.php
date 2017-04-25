<?php

namespace MarkNotes\View;

defined('_MARKNOTES') or die('No direct access allowed');

include 'libs/autoload.php';

class Toolbar
{
    protected static $hInstance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new Toolbar();
        }

        return self::$hInstance;
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

        // Call plugins that are responsible to add icons to the content toolbar
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->loadPlugins('buttons', 'content');
        $icons = '';
        $args = array(&$icons);
        $aeEvents->trigger('add.buttons', $args);

        $toolbar =
            '<div id="toolbar-button" data-toolbar="style-option" '.
                'class="onlyscreen btn-toolbar btn-toolbar-default">'.
                '<i class="fa fa-cog"></i>'.
            '</div>'.
            '<div id="toolbar-options" class="hidden btn-toolbar-warning">'.$icons.'</div>';

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
