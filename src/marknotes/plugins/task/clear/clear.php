<?php

/**
 * Clear the session.  Called by the 'Eraser' button, located in the option button of the
 * treeview pane
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Clear
{
    public static function run()
    {
        $aeSession = \MarkNotes\Session::getInstance();

        // When the task is 'clear', just clear the session
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
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('run.task', __CLASS__.'::run');

        return true;
    }
}
