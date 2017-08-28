<?php

/**
 * This plugin will display an anchor icon after each headings so, clicking on
 * that icon will put the anchor name in the URL for easy reference
 * (like page.html#the-title-name)
 *
 * Based on anchor-js; https://ben.balter.com/2014/03/13/pages-anchor-links/
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Anchor
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

		$js .= "\n<script type=\"text/javascript\" src=\"".$root."/libs/anchor-js/anchor.min.js\"></script>\n".
		   "\n<script type=\"text/javascript\">anchors.add('h2, h3, h4, h5, h6');</script>\n";

		if ($aeSettings->getDebugMode()) {
			$js .= "<!-- End for ".__FILE__."-->";
		}

		return true;
	}

    /**
     * Provide additionnal stylesheets
     */
    public static function addCSS(&$css = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        if ($aeSettings->getDebugMode()) {
            $css .= "\n<!-- Lines below are added by ".__FILE__."-->";
        }

        $css .=
            "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/libs/anchor-js/anchor.css\">\n";

        if ($aeSettings->getDebugMode()) {
            $css .= "<!-- End for ".__FILE__."-->";
        }

        return true;
    }
    /**
     * Attach the function and responds to events
     */
    public function bind() : bool
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // This plugin is only needed when the task is one of the following
        if (!in_array($task, array('display','html'))) {
            return false;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        return true;
    }
}
