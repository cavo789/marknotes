<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Editor
{

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $js .=
            "<script type=\"text/javascript\" src=\"".$root."/libs/simplemde/simplemde.min.js\"></script>\n".
            "<script type=\"text/javascript\" src=\"".$root."/plugins/content/html/editor/editor.js\"></script>\n";


        // Add text for the editor
        $aeSettings = \MarkNotes\Settings::getInstance();
        $js .=
            "marknotes.message.button_encrypt='".$aeSettings->getText('button_encrypt', 'Add encryption for the selection', true)."';\n".
            "marknotes.message.button_exit_edit_mode='".$aeSettings->getText('button_exit_edit_mode', 'Exit the editing mode', true)."';\n".
            "marknotes.message.button_save='".$aeSettings->getText('button_save', 'Submit your changes', true)."';\n".
            "marknotes.message.button_save_done='".$aeSettings->getText('button_save_done', 'The file has been successfully saved', true)."';\n".
            "marknotes.message.button_save_error='".$aeSettings->getText('button_save_error', 'There was an error while saving the file', true)."';\n";

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

        // The editor plugin is only needed when the task is 'display' i.e. when the note
        // is displayed through the interface
        if ($task !== 'display') {
            return true;
        }
        
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        return true;
    }
}
