<?php

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Files
{

    /**
     * Create or Rename a file / folder
     */
    private static function createRename(&$params = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $filename = $aeSession->get('filename');

        $newname = json_decode(urldecode($aeFunctions->getParam('param2', 'string', '', true)));
        if ($newname != '') {
            $newname = $aeFiles->sanitizeFileName(trim($newname));
        }

        $type = $aeFunctions->getParam('param3', 'string', '', false);

        $aeTask = \MarkNotes\Tasks\AddOrRename::getInstance();

        $return = $aeTask->run(array('oldname' => $filename,'newname' => $newname,'type' => $type));
    }

    /**
     * Delete a file / folder
     */
    private static function delete(&$params = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $filename = $aeSession->get('filename');

        $type = $aeFunctions->getParam('param3', 'string', '', false);

        $return = __FILE__.' - '.__LINE__.' - Kill '.$filename. ' type='.$type;

        //$aeTask = \MarkNotes\Tasks\Delete::getInstance();
        //$return = $aeTask->run(array('filename' => $filename,'type' => $type));

        return $return;
    }

    public static function run(&$params = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $task = $aeSession->get('task');
        $return = '';

        switch ($task) {
            case 'files.rename':
                $return = self::createRename();
                break;
            case 'files.delete':
                $return = self::delete();
                break;
        }

        header('Content-Type: application/json');
        echo $return;

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
            $aeEvents->bind('run.task', __CLASS__.'::run');
        //} else {
        //    $sReturn = $aeFunctions->showError('not_authenticated', 'You need first to authenticate');
        }

        return true;
    }
}
