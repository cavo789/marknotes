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
    public static function trigger(string $event = '', array &$args = null) : bool
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        /*<!-- build:debug -->*/
        /*if ($aeSettings->getDevMode()) {
            //$arr = array();
            $arr = array('markdown.read','render.js','render.css');

            // Only for a few events...
            if (!in_array($event, $arr)) {
                $aeDebug->here('###DevMode### - Trigger Event '.$event, 2);
                if (count(self::$arrEvents[$event]) > 0) {
                    //echo '<pre style="background-color:yellow;color:red;padding-left:50px;">'.__FILE__.' - '.
                        __LINE__.' '.print_r(self::$arrEvents[$event], true).'</pre>';
                } else {
                    echo '<pre style="background-color:yellow;">No functions attached</pre>';
                }
            }
        }*/
        /*<!-- endbuild -->*/

        if (isset(self::$arrEvents[$event])) {
            if (count(self::$arrEvents[$event]) > 0) {
                foreach (self::$arrEvents[$event] as $func) {
                    if (is_callable($func)) {

                        /*<!-- build:debug -->*/
                        /*if ($aeSettings->getDevMode()) {
                            //if ($event === 'export.slides') {
                                $aeDebug->here('###DevMode### - Event '.$event.', call '.$func, 5);
                            //}
                        }*/
                        /*<!-- endbuild -->*/

                        call_user_func_array($func, $args);

                        $bStopProcessing = false;

                        if (isset($args['stop_processing']) && (substr($event, 0, 5) === 'export')) {

                            // If the file (f.i. the pdf output) has been created, there is
                            // no need to continue and use the second converter.
                            //
                            // For instance, see below, if "pandoc" which is called first
                            // has successfully created the PDF there is no need to continue with
                            // dompdf
                            //
                            // "plugins": {
                            //		"content": {
                            //			"pdf": ["pandoc", "dompdf"]

                            $bStopProcessing = ($args['stop_processing'] == 1);
                        }

                        if ($bStopProcessing) {
                            break;
                        }
                    } else {

                        // OUCH ! The function isn't callable
                        // see the sample in /marknotes/plugins/content/html/replace.php
                        // The call should be something like this :
                        //    $aeEvents = \MarkNotes\Events::getInstance();
                        //    $aeEvents->bind('display.html', __CLASS__.'::doIt');
                        // The full qualified function name i.e. the name space, the class name and the
                        // function name without the parenthesis
                        /*<!-- build:debug -->*/
                        if ($aeSettings->getDevMode()) {
                            $aeDebug->here('###DevMode### - Event '.$event.', '.$func.' is not callable, ERROR', 5);
                        }
                        /*<!-- endbuild -->*/
                    }
                }
            }
        }

        return true;
    }

    /**
     * Add a function (a "callable" one) into the list of listeners for a specific event,
     * like 'display.html' or 'render.js'.
     *
     * Use !in_array to be sure that the same function is there only once
     */
    public static function bind(string $event = '', string $func)
    {
        /*<!-- build:debug -->*/
        /*$aeSettings = \MarkNotes\Settings::getInstance();
        if ($aeSettings->getDevMode()) {
            $aeDebug = \MarkNotes\Debug::getInstance();
            $aeDebug->here('###DevMode### - Bind Event '.$event, 3);
        }*/
        /*<!-- endbuild -->*/

        if (isset(self::$arrEvents[$event])) {
            if (!in_array($func, self::$arrEvents[$event])) {
                self::$arrEvents[$event][] = $func;
            }
        } else {
            // This event isn't yet known
            self::$arrEvents[$event][] = $func;
        }
    }

    private static function getNameSpaceAndClassName($file)
    {
        $sReturn = null;

        $content = file_get_contents($file);

        if (preg_match('/^namespace (.*);/m', $content, $matches)) {
            $sReturn = '\\'.trim($matches[1]);
        }

        if (preg_match('/^class (.*)$/m', $content, $matches)) {
            $sReturn .= '\\'. trim($matches[1]);
        }

        return $sReturn;
    }

    public static function loadPlugins(string $type = 'content', string $layout = '')
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        if ($type !== '') {

            // The plugins folder is at the root level and not under /marknotes
            $dir = rtrim(dirname(__DIR__), DS).DS.'plugins'.DS.$type.DS;

            if ($layout !== '') {
                $dir = $dir.$layout.DS;
            }

            if (is_dir($dir)) {
                $aeFiles = \MarkNotes\Files::getInstance();

                // Get the list of plugins (f.i. of type 'content')
                if ($type !== 'task') {
                    $plugins = $aeSettings->getPlugins($type, $layout);
                } else {
                    $plugins = str_replace($dir, '', array_filter(glob($dir.'*'), 'is_file'));
                }
                // And if the plugin exists on the filesystem, load it
                foreach ($plugins as $plugin) {
                    if (substr($plugin, -4) !== '.php') {
                        $plugin .= '.php';
                    }

                    if ($aeFiles->fileExists($file = $dir.$plugin)) {

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
            } else {

                // Should be anormal

                /*<!-- build:debug -->*/
                if ($aeSettings->getDevMode()) {
                    $aeDebug = \MarkNotes\Debug::getInstance();
                    $aeDebug->here('###DevMode### - Folder '.$dir.' not found', 5);
                }
                /*<!-- endbuild -->*/
            }
        }

        return true;
    }
}
