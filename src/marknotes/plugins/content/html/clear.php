<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Clear
{

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        if ($aeSettings->getDebugMode()) {
            $js .= "\n<!-- Lines below are added by ".__FILE__."-->";
        }
        $js .=
            "\n<script type=\"text/javascript\">\n".
            "marknotes.message.settings_clean_done='".$aeSettings->getText('settings_clean_done', 'The application\'s cache has been cleared', true)."';\n".
            "marknotes.settings.use_localcache=".($aeSettings->getUseLocalCache()?1:0).";\n".
            "</script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/marknotes/plugins/content/html/clear/clear.js\"></script>\n";

        if ($aeSettings->getDebugMode()) {
            $js .= "<!-- End for ".__FILE__."-->";
        }
        return true;
    }

    public static function run(&$params = null)
    {

        // Just destroy the session (server cache)
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSession->destroy();

        header('Content-Type: application/json');
        echo json_encode(array('status' => '1'));

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
		$aeSession = \MarkNotes\Session::getInstance();
		$task = $aeSession->get('task', '');

		// This plugin is needed only for these tasks : main, display and html

		if (!in_array($task, array('main', 'display', 'html'))) {
			return false;
		}

        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'optimisation');

        $localStorage = boolval($arrSettings['localStorage'] ?? false);
        $serverSession = boolval($arrSettings['server_session'] ?? false);

        // If there is no cache (on the client-side with localStorage or on the server side),
        // the Clear cache button isn't needed
        if (($localStorage === false) && ($serverSession === false)) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('run.task', __CLASS__.'::run');
        return true;
    }
}
