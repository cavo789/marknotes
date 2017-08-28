<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class DataTables
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
           "marknotes.message.datatable_copy='".$aeSettings->getText('datatable_copy', '', true)."';\n".
           "marknotes.message.datatable_copyTitle='".$aeSettings->getText('datatable_copyTitle', '', true)."';\n".
           "marknotes.message.datatable_copyKeys='".$aeSettings->getText('datatable_copyKeys', '', true)."';\n".
           "marknotes.message.datatable_copySuccess_One='".$aeSettings->getText('datatable_copySuccess_One', '', true)."';\n".
           "marknotes.message.datatable_copySuccess_Many='".$aeSettings->getText('datatable_copySuccess_Many', '', true)."';\n".
           "</script>\n".
            "\n<script type=\"text/javascript\" src=\"".$root."/libs/DataTables/js/jquery.dataTables.min.js\"></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/libs/DataTables/js/dataTables.bootstrap4.min.js\"></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/libs/DataTables/js/dataTables.buttons.min.js\"></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/libs/DataTables/js/buttons.html5.min.js\"></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/marknotes/plugins/content/html/datatables/datatables.js\"></script>\n";


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
            "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/libs/DataTables/css/dataTables.bootstrap4.min.css\">\n".
            "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/libs/DataTables/css/buttons.bootstrap.min.css\">\n".
            "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/libs/DataTables/css/buttons.dataTables.min.css\">\n";

        if ($aeSettings->getDebugMode()) {
            $css .= "<!-- End for ".__FILE__."-->";
        }

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
        // There is no need for interaction with the table when the output format is pdf

        if (in_array($task, array('pdf','reveal','remark'))) {
            return false;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        return true;
    }
}
