<?php
/**
 *	For converting plain text (emails, urls, ...) into links
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Linkify
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
            "\n<script type=\"text/javascript\" src=\"".$root."/libs/linkify/linkify.min.js\"></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/libs/linkify/linkify-jquery.min.js\"></script>\n". "<script type=\"text/javascript\" src=\"".$root."/plugins/content/html/linkify/linkify.js\"></script>\n";

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

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $css .=
            "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/libs/simplemde/simplemde.min.css\" />\n";

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // This plugin is not needed when the task is f.i. 'pdf'
        // There is no need for converting to links with the table when the output format is pdf

        if (in_array($task, array('pdf','reveal','remark'))) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        return true;
    }
}
