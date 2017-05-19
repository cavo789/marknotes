<?php

/**
 * The treeview is being displayed, add extra js scripts if needed
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Treeview
{
    public static function display(&$params = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $task = $aeSession->get('task');
        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        if ($aeSettings->getDebugMode()) {
            $js = "\n<!-- Lines below are added by ".__FILE__."-->";
        } else {
            $js = '';
        }

        $js .= "\n<script type=\"text/javascript\" ". "src=\"".$root."/marknotes/plugins/task/treeview/assets/treeview.js\"></script>\n";

        $js .=
            "<script type=\"text/javascript\">".
            "marknotes.message.tree_delete_file='".$aeSettings->getText('tree_delete_file', 'Delete the note [%s]', true)."';\n".
            "marknotes.message.tree_delete_folder='".$aeSettings->getText('tree_delete_folder', 'Delete the note [%s]', true)."';\n".
            "marknotes.message.tree_delete_file_confirm='".$aeSettings->getText('tree_delete_file_confirm', 'Are you really sure you want to delete the note [%s] ?', true)."';\n".
            "marknotes.message.tree_delete_folder_confirm='".$aeSettings->getText('tree_delete_folder_confirm', 'Are your really sure you want to kill everything in the folder [%s] and the folder itself ?', true)."';\n".
            "marknotes.message.tree_new_folder='".$aeSettings->getText('tree_new_folder', 'Create a new folder', true)."';\n".
            "marknotes.message.tree_new_folder_name='".$aeSettings->getText('tree_new_folder_name', 'New folder', true)."';\n".
            "marknotes.message.tree_new_note='".$aeSettings->getText('tree_new_note', 'Create a new note', true)."';\n".
            "marknotes.message.tree_new_note_name='".$aeSettings->getText('tree_new_note_name', 'New note', true)."';\n".
            "</script>";

        if ($aeSettings->getDebugMode()) {
            $js .= "\n<!-- End for ".__FILE__."-->";
        }

        $params['js'] = $js;

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();

        // Only when the user is connected
        if ($aeSession->get('authenticated', 0) === 1) {
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->bind('display', __CLASS__.'::display');
        }

        return true;
    }
}
