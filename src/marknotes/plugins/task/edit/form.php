<?php
/**
 * Generate the form for the editor; based on SimpleMDE
 * @link https://github.com/sparksuite/simplemde-markdown-editor
 */
namespace MarkNotes\Plugins\Task\Export;

defined('_MARKNOTES') or die('No direct access allowed');

class Form extends \MarkNotes\Plugins\Task\Plugin
{
    protected static $me = __CLASS__;
    protected static $json_settings = 'plugins.task.edit';
    protected static $json_options = '';

    /**
     * Determine if this plugin is needed or not
     */
    final protected static function canRun() : bool
    {
        $bCanRun = parent::canRun();

        if ($bCanRun) {
            $aeSession = \MarkNotes\Session::getInstance();
            $bCanRun = boolval($aeSession->get('authenticated', 0));
        }

        if (!$bCanRun) {
            $aeSettings = \MarkNotes\Settings::getInstance();

            $return = array();
            $return['status'] = 0;
            $return['message'] = $aeSettings->getText('not_authenticated', 'You need first to authenticate', true);

            header('Content-Type: application/json');
            echo json_encode($return, JSON_PRETTY_PRINT);
        }

        return $bCanRun;
    }

    /**
     * Return the code for showing the login form and respond to the login action
     */
    public static function run(&$params = null) : bool
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        header('Content-Type: text/plain; charset=utf-8');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Get the filename from the querystring
        $filename = $aeFunctions->getParam('param', 'string', '', true);
        $filename = json_decode(urldecode($filename));

        // Be sure to have the .md extension
        $filename = $aeFiles->RemoveExtension($filename).'.md';

        // Derive the fullname
        $doc = $aeSettings->getFolderDocs(true);

        $fullname = str_replace('/', DS, ($doc.ltrim($filename, DS)));

        if (!$aeFiles->fileExists($fullname)) {
            echo str_replace('%s', '<strong>'.$filename.'</strong>', $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists'));
            die();
        }

        // In the edit form; keep encrypted data ... unencrypted (we need
        // to be able to see and update them)
        $params['encryption'] = 0;

        $aeMD = \MarkNotes\FileType\Markdown::getInstance();
        $markdown = $aeMD->read($fullname, $params);

        // Get the default language
        $lang=$aeSettings->getLanguage();

        // and now, try to retrieve the language used in the note;
        // this from the YAML block if present
        $yaml = $aeSession->get('yaml', array());

        if ($yaml !== array()) {
            $lib=$aeSettings->getFolderLibs()."symfony/yaml/Yaml.php";
            if (is_file($lib)) {
                include_once $lib;
                $arrYAML =  \Symfony\Component\Yaml\Yaml::parse($yaml);
                $lang = $arrYAML['language']??$lang;
            }
        }

        $sEditForm =
            '<div class="row">'.
                '<div class="col-md-12">'.
                    '<div class="box">'.
                        '<div class="box-header">'.
                            '<h3 class="box-title">'.utf8_encode($fullname).'</h3>'.
                            '<div class="pull-right box-tools">
								<button type="button" class="btn btn-default btn-sm btn-exit-editor">
								<i class="fa fa-times"></i></button>
							</div>'.
                        '</div>'.
                        '<div class="box-body pad">'.
                            '<div class="editor-wrapper">'.
                                '<textarea id="sourceMarkDown" lang="'.$lang.'" 		spellcheck="true">'.$markdown.'</textarea>'.
                            '</div>'.
                        '</div>'.
                    '</div>'.
                '</div>'.
            '</div>';

        echo $sEditForm;

        return true;
    }
}
