<?php

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Events
{
    protected static $hInstance = null;

    private static $arrEvents = array();

    public function __construct()
    {
        self::$arrEvents = array();
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new Events();
        }
        return self::$hInstance;
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
        if ($aeSettings->getDebugMode()) {
            $aeDebug->log('Trigger started for ['.$event.']', 'debug');
            if ($aeSettings->getDevMode()) {
                if (!isset(self::$arrEvents[$event])) {
                    $aeDebug->log('There is no listener for the '.$event, 'debug');
                }
            }
        }
        /*<!-- endbuild -->*/

        if (isset(self::$arrEvents[$event])) {
            if (count(self::$arrEvents[$event]) > 0) {
                foreach (self::$arrEvents[$event] as $func) {
                    if (is_callable($func)) {

                        /*<!-- build:debug -->*/
                        if ($aeSettings->getDebugMode()) {
                            $aeDebug->log('   call ['.$func.']', 'debug');
                        }
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
                            $aeDebug->here('Event '.$event.', '.$func.' is not callable, ERROR', 5);
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
        $aeSettings = \MarkNotes\Settings::getInstance();
        if ($aeSettings->getDevMode()) {
            $aeDebug = \MarkNotes\Debug::getInstance();
            $aeDebug->log('Add a listener for '.$event);
        }
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

    public static function loadPlugins(string $type = 'content', string $subtask = '')
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        if ($type !== '') {

            // The plugins folder is under /marknotes
            $dir = rtrim(dirname(__DIR__), DS).'/marknotes/plugins/'.$type.DS;

            if ($subtask !== '') {
                // can be edit.save => retrieve the first part : edit
                if (($wPos = strpos($subtask, '.')) !== false) {
                    $subtask = substr($subtask, 0, $wPos);
                }
                $dir = $dir.$subtask.DS;
            }


            if (is_dir($dir)) {
                $aeFiles = \MarkNotes\Files::getInstance();

                // Get the list of plugins (f.i. of type 'content')
                if ($type !== 'task') {
                    $plugins = $aeSettings->getPlugins($type, $subtask);
                } else {
                    $tmp = str_replace($dir, '', array_filter(glob($dir.'*'), 'is_file'));
                    $plugins = array();
                    foreach ($tmp as $plugin) {
                        $plugins[] = array($plugin => 1);
                    }
                }

                // And if the plugin exists on the filesystem, load it
                if (count($plugins) > 0) {
                    foreach ($plugins as $plugin) {

                    // plugins is an array with two entries : the name of the plugin (f.i. gtranslate)
                    // and a boolean 1/0 for "is this plugin enabled or not".

                        foreach ($plugin as $name => $enabled) {
                            if (substr($name, -4) !== '.php') {
                                $name .= '.php';
                            }

                            if (($enabled === 1) && ($aeFiles->fileExists($file = $dir.$name))) {

                                /*<!-- build:debug -->*/
                                $aeSettings = \MarkNotes\Settings::getInstance();
                                if ($aeSettings->getDevMode()) {
                                    $aeDebug = \MarkNotes\Debug::getInstance();
                                    $aeDebug->log('Load the plugin '.$file);
                                }
                                /*<!-- endbuild -->*/

                                // Load the plugin
                                require_once($file);

                                // And retrieve its namespace and class name
                                // f.i. "\MarkNotes\Plugins\Content\HTML\ReplaceVariables"
                                $class = self::getNameSpaceAndClassName($file);

                                // Instanciate the class (plugin)
                                $plug = new $class;

                                // and run the bind() function
                                $plug->bind();
                            } // foreach ($plugin as $name => $enabled)
                        } // foreach
                    } // foreach ($plugins as $plugin)
                } // if(count($plugins)>0)
            } else {

                // Should be anormal

                /*<!-- build:debug -->*/
                if ($aeSettings->getDevMode()) {
                    $aeDebug = \MarkNotes\Debug::getInstance();
                    $aeDebug->here('Folder '.$dir.' not found', 5);
                }
                /*<!-- endbuild -->*/
            }
        }

        return true;
    }
}
