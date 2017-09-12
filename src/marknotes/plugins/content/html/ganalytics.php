<?php

/**
 * Google Analytics plugin for Marknotes
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class GAnalytics
{
    /**
     * Add new scripts in the <script> part of the page; add the Google Analytics script
     */
    public static function addJS(&$js = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

        $arr = $aeSettings->getPlugins();

        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'ganalytics');

        // Don't load if no code has been provided
        $analyticsCode = $arrSettings['code'] ?? '';

        if ($aeSettings->getDebugMode()) {
            $js .= "\n<!-- Lines below are added by ".__FILE__."-->";
        }

        $js .= "\n<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){ (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o), m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m) })(window,document,'script','https://www.google-analytics.com/analytics.js','ga'); ga('create', '".$analyticsCode."', 'auto'); ga('send', 'pageview');</script>\n";

        if ($aeSettings->getDebugMode()) {
            $js .= "<!-- End for ".__FILE__."-->";
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind(): bool
    {

		$aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // This plugin is needed only for these tasks : main, display and html

        if (!in_array($task, array('main', 'display', 'html'))) {
            return false;
        }

        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'ganalytics');

        // Don't load if no code has been provided
        $analyticsCode = $arrSettings['code'] ?? '';
        if ($analyticsCode === '') {
            return false;
        }

        // Check if, in the settings, enable_localhost is set to 1 (default value)
        // If set to 0, don't load the plugin on localhost system
        $localhost = $arrSettings['enable_localhost'] ?? 1;

        // localhost is equal to 1 ? Always load the plugin
        $bLoad = ($localhost == 1);

        if (!$bLoad) {

            // Check if we're on localhost, if so, don't load the plugin
            $bLoad = !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1','::1'));

            // Check name too
            if ($bLoad) {
                $bLoad = ($_SERVER['SERVER_NAME'] !== 'localhost');
            }
        }

        if ($bLoad) {
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->bind('render.js', __CLASS__.'::addJS');
            return true;
        } else {
            return false;
        }
    }
}
