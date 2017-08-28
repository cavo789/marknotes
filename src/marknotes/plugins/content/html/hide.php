<?php

/**
 * Hide the treeview
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Hide
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
            "<script type=\"text/javascript\" src=\"".$root."/marknotes/plugins/content/html/hide/hide.js\"></script>\n";

        if ($aeSettings->getDebugMode()) {
            $js .= "<!-- End for ".__FILE__."-->";
        }
        return true;
    }

	/**
	 * Provide additionnal css
	 */
	public static function addCSS(&$css = null)
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

		$css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/marknotes/plugins/content/html/hide/hide.css\" />\n";

		return true;
	}

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
		$aeSession = \MarkNotes\Session::getInstance();
		$task = $aeSession->get('task', '');

		// This plugin is needed only for these tasks

		if (!in_array($task, array('main', 'display'))) {
			return false;
		}

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
		$aeEvents->bind('render.css', __CLASS__.'::addCSS');
        return true;
    }
}
