<?php
/*
 * Definition of a button plugin - Define the global structure
 * and features. This class will be derived by plugins if f.i.
 * /plugins/buttons/page/content/docx.php
 */
namespace MarkNotes\Plugins\Button;

defined('_MARKNOTES') or die('No direct access allowed');

abstract class Plugin
{
    /**
     * The child class should implement the add() function
     */
    abstract public static function add(&$buttons = array()) : bool;

    /**
     * Constructor
     */
    public function __construct()
    {
        // The child class should have a line like below in his definition
        //	 protected static $me = __CLASS__;
        if (!isset(static::$me)) {
            throw new \Exception(get_class($this).' must have a $me '.
                'property and must initialize it exactly like this : "protected static $me = __CLASS__"');
        }

        // The child class should have a line like below in his definition
        //	 protected static $json_settings = 'plugins.buttons.page.content.edito';
        if (!isset(static::$json_settings)) {
            throw new \Exception(get_class($this).' must have a $json_settings '.
                'property and must initialize it (f.i. '. '"plugins.buttons.page.content.editor"). '.
                'That property indicates where in the settings.json '.
                'file marknotes can find the settings '.
                '(enabled, not_if_task, only_if_task, ...) for that plugin');
        }

        // The child class should have a line like below in his definition
        //	 protected static $json_linked = 'plugins.page.html.editor';
        if (!isset(static::$json_linked)) {
            throw new \Exception(get_class($this).' must have a $json_linked '.
                'property and must initialize it to the "linked_plugin" '.
                '(f.i. "plugins.page.html.editor"). '.
                'A button plugin indeed just add a button that will often '.
                'call a javascript function and that function should exists. '.
                'Due to the plugins\'s architecture of marknotes, such '.
                'functions are added to the page thanks a page plugin and, thus, '.
                'the page plugin key should be mentioned in $json_linked.');
        }

        return true;
    }

    /**
     * Generate the code for a button.
     *
     * $params is an array with properties :
     *
     *		'group'		: name of the buttons group (export, utility, ...)
     *		'title'		: name of the variable in the language file
     *						title for the button
     *		'default'	: if the 'title variable' isn't found, use a default
     *						title (in english)
     *		'id'		: (optional) if present, ID to use for
     *						the button
     *		'task'		: (optional) if present, data-task to
     *						assign to the button
     *		'extension' : (optional) if present, data-extension to
     *						assign to the button
     *		'extra'		: (optional) if present, will be added as is
     *		'icon'		: font-awesome icon (f.i. fa-cog, fa-print, ...)
     *		'intro'		: (optional) text for the intro.js
     *		'quickIcons': (optional) 1 if the icon should be displayed
     * 					  immediately in the interface, near the "cog"
     *					 	button
     */
    protected static function button(array $params) : array
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $title = $aeSettings->getText($params['title'], $params['default']??'', false);

        $extension = '';
        $task = '';
        $id = '';

        // When 'name' is defined, retrieved the options from settings.json
        $arrOptions = array();
        if (isset($params['name'])) {
            $name = $params['name'];
            $arrOptions = $aeSettings->getPlugins('buttons.'.$name, array());
        }

        if (isset($params['id'])) {
            $id = 'id="'.$params['id'].'" ';
        }

        if (isset($params['task'])) {
            $task = 'data-task="'.$params['task'].'" ';
        }

        if (isset($params['extension'])) {
            $extension = 'data-extension="'.$params['extension'].'" ';
        }

        // If arrOptions is defined, get the QuickIcons flag from
        // settings.json
        $quickIcons = $arrOptions['quickIcons'] ?? 0;

        // And perhaps overridden in the $params
        if (isset($params['quickIcons'])) {
            $quickIcons  = boolval($params['quickIcons'])?1:0;
        }

        $data_intro='';
        if (isset($params['intro'])) {
            $data_intro = 'data-intro="'.str_replace('"', '\"', $params['intro']).'" ';
        }

        $anchor = '<a '.
            $id.' '.
            $task.' '.
            $extension.' '.
            'title="'.ucfirst($title).'" '.
            $data_intro.
            (isset($params['extra']) ? $params['extra'].' ' : '').'>'.
            '%1'.
            '</a>';
            
        $button = array(
            'icon'	=>'fa-'.$params['icon'],
            'anchor' => $anchor,
            'button' =>
                str_replace('%1', '<i class="fa fa-'.$params['icon'].'" 	aria-hidden="true"></i>', $anchor),
            'title'  => ucfirst($title),
            'quickIcons' => $quickIcons
        );

        return $button;
    }

    /**
     * The plugin button can be enabled ONLY if the associated page HTML
     * plugin is enabled. This because the button will call a javascript
     * function that is implemented in a .js file; loaded by a page HTML
     * plugin (the "linked plugin")
     */
    private static function linkedPlugin() : bool
    {
        $bReturn = true;

        if (trim(static::$json_linked)!=='') {
            // Check that the linked plugin is also enabled

            $aeSettings = \MarkNotes\Settings::getInstance();
            $arrSettings = $aeSettings->getPlugins(static::$json_linked);
            $bReturn = boolval($arrSettings['enabled']?? 0);

            if (!$bReturn) {
                /*<!-- build:debug -->*/
                if ($aeSettings->getDebugMode()) {
                    $aeDebug = \MarkNotes\Debug::getInstance();

                    $str = "The plugin is enabled [".
                        static::$json_settings."] but requires that the page ".
                        "plugin [".static::$json_linked."] is enabled too and ".
                        "it's not the case. Both should be enabled or ".
                        "disabled in the same time...";

                    $aeDebug->log($str, "warning");

                    if ($aeDebug->getDevMode()) {
                        $aeDebug->here(DEV_MODE_PREFIX." ".$str, 10);
                        die();
                    }
                }
                /*<!-- endbuild -->*/
            }
        }

        return $bReturn;
    }

    /**
     * Determine if the plugin can add his button
     */
    protected static function canAdd() : bool
    {
        $bReturn = true;
        if (static::$json_linked!=='') {
            $bReturn = self::linkedPlugin();
        }

        return $bReturn;
    }

    /**
     * Capture the add.button event and attach the add() function
     */
    public function bind(string $plugin) : bool
    {
        if ($this->canAdd()) {
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->bind('add.buttons', static::$me.'::add', $plugin);
        }
        return true;
    }
}
