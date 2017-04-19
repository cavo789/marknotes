<?php

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Events
{
    protected static $_Instance = null;

    private static $arrEvents = array();

    public function __construct()
    {
        self::$arrEvents = array();
        return true;
    }

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new Events();
        }
        return self::$_Instance;
    }

    /**
     * Remove any bindings functions, reset events
     */
    public static function reset()
    {
        self::$arrEvents = array();
        return true;
    }

    /**
     * Call an event and fires every attached functions if there are somes
     */
    public static function trigger(string $event = '', array &$args = null)
    {
        if (isset(self::$arrEvents[$event])) {
            if (count(self::$arrEvents[$event]) > 0) {
                foreach (self::$arrEvents[$event] as $func) {
                    if (is_callable($func)) {
                        call_user_func_array($func, $args);
                    } else {
                        // OUCH ! The function isn't callable
                        // see the sample in /marknotes/plugins/content/html/replace.php
                        // The call should be something like this :
                        //    $aeEvents = \MarkNotes\Events::getInstance();
                        //    $aeEvents->bind('display.html', __CLASS__.'::doIt');
                        // The full qualified function name i.e. the name space, the class name and the
                        // function name without the parenthesis
                    }
                }
            }
        }
    }

    public static function bind(string $event = '', string $func)
    {
        self::$arrEvents[$event][] = $func;
    }

    private static function getNameSpaceAndClassName($file)
    {
        $sReturn = null;

        $content = file_get_contents($file);

        if (preg_match('/^namespace (.*);$/m', $content, $matches)) {
            $sReturn = '\\'.$matches[1];
        }
        if (preg_match('/^class (.*)$/m', $content, $matches)) {
            $sReturn .= '\\'. $matches[1];
        }

        return $sReturn;
    }

    public static function loadPlugins(string $type = 'content', string $layout = '')
    {
        if ($type !== '') {

            // The plugins folder is at the root level and not under /marknotes
            $dir = rtrim(dirname(__DIR__), DS).DS.'plugins'.DS.$type.DS;

            if ($layout !== '') {
                $dir = $dir.$layout.DS;
            }

            if (is_dir($dir)) {
                $aeFiles = \MarkNotes\Files::getInstance();
                $aeSettings = \MarkNotes\Settings::getInstance();

                // Get the list of plugins (f.i. of type 'content')
                $plugins = $aeSettings->getPlugins($type, $layout);

                // And if the plugin exists on the filesystem, load it
                foreach ($plugins as $plugin) {
                    if ($aeFiles->fileExists($file = $dir.$plugin.'.php')) {

                        // Load the plugin
                        require_once($file);

                        // And retrieve its namespace and class name
                        // f.i. "\MarkNotes\Plugins\Content\HTML\ReplaceVariables"
                        $class = self::getNameSpaceAndClassName($file);

                        // Instanciate the class (plugin)
                        $plug = new $class;

                        // and run the bind() function
                        $plug->bind();
                    }
                }
            }
        }

        return true;
    }
}
