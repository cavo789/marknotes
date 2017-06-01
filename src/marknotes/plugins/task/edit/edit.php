<?php

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Edit
{
    /**
     * Return the code for showing the login form and respond to the login action
     */
    public static function getForm(array &$params = array()) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        header('Content-Type: text/plain; charset=utf-8');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3) != '.md') {
            $params['filename'] .= '.md';
        }

        $fullname = str_replace('/', DS, ($aeSettings->getFolderDocs(true).ltrim($params['filename'], DS)));

        if (!$aeFiles->fileExists($fullname)) {
            echo str_replace('%s', '<strong>'.$fullname.'</strong>', $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists'));
            die();
        }

        $aeSession->set('editMode', 1);

        $aeMD = \MarkNotes\FileType\Markdown::getInstance();
        $markdown = $aeMD->read($fullname, $params);

        $sReturn = '<div class="editor-wrapper"><strong class="filename">'.utf8_encode($fullname).'</strong><textarea id="sourceMarkDown">'.$markdown.'</textarea></div>';

        return $sReturn;
    }

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $js .=
            "<script type=\"text/javascript\">".
            "marknotes.message.incorrect_login='".$aeSettings->getText('login_error', 'Incorrect login, please try again', true)."';\n".
            "marknotes.message.login_success='".$aeSettings->getText('login_success', 'Login successfull', true)."';\n".
            "</script>";

        return true;
    }

    /*
     * Save new content (after edition by the user)
     * Called by the editor, responds to the save button.
     */
    private static function save(&$params = null)
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            $aeDebug->log('Saving the note\'s content', 'debug');
        }
        /*<!-- endbuild -->*/

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3) != '.md') {
            $params['filename'] .= '.md';
        }

        $fullname = str_replace('/', DIRECTORY_SEPARATOR, $aeSettings->getFolderDocs(true).ltrim($params['filename'], DS));

        // Call content plugins
        $markdown = json_decode(urldecode($aeFunctions->getParam('markdown', 'string', '', true)));
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->loadPlugins('markdown');
        $args = array(&$markdown);
        $aeEvents->trigger('markdown.write', $args);
        $params['markdown'] = $args[0];

        $status = array('success' => 1,'message' => $aeSettings->getText('button_save_done', 'The file has been successfully saved'));

        $return = array();
        $return['status'] = $status;
        $return['filename'] = $fullname;

        return json_encode($return, JSON_PRETTY_PRINT);
    }

    public static function run(&$params = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        // Only when the user is connected
        if ($aeSession->get('authenticated', 0) === 1) {
            $task = $params['task'] ?? '';

            $sReturn = '';
            switch ($task) {
                case 'edit.form':
                    $sReturn = self::getForm($params);
                    break;
                case 'edit.save':
                    $sReturn = self::save($params);
                    header('Content-Type: application/json');
                    break;
            } // switch
        } else {
            $sReturn = $aeFunctions->showError('not_authenticated', 'You need first to authenticate', true);
        }

        echo $sReturn;

        return true;
    }
    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'login');

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('run.task', __CLASS__.'::run');
        return true;
    }
}
