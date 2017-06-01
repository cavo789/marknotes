<?php

/**
 * Return a dynamic index.html page : the user will be able to address any folders just by
 * typing the URL like http://site/docs/folder/subfolder and followed by index.html.
 *
 * That index.html file doesn't exists and will be generated (html rendering) by this
 * class.
 *
 * The content of the /templates/index.php file will be taken (as template) and the list of
 * .md files immediatly in the adressed folder (and not in subfolders) will be collected.
 *
 * This task will generate a <ul><li> list and append in into the template then return the
 * html.
 *
 * Example : http://localhost/marknotes/docs/CMS/Joomla/index.html will display the list of .md
 * notes of /docs/CMS/Joomla
*/

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

// For third parties libraries
include 'libs/autoload.php';

class Index
{
    protected static $hInstance = null;

    public function __construct()
    {
        return true;
    } // function __construct()

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new Index();
        }

        return self::$hInstance;
    } // function getInstance()

    public function run(array $params)
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeHTML = \MarkNotes\FileType\HTML::getInstance();
        $aeMD = \MarkNotes\FileType\Markdown::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $docRoot = str_replace(DS, '/', $aeSettings->getFolderDocs(false));

        // The filename shouldn't mention the docs folders, just the filename
        // So, $filename should not be docs/markdown.md but only markdown.md because the
        // folder name will be added later on
        if (substr($params['filename'], 0, strlen($docRoot)) === $docRoot) {
            $params['filename'] = substr($params['filename'], strlen($docRoot));
        }

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -5) != '.html') {
            $params['filename'] .= '.html';
        }

        $fullname = str_replace(
            '/',
            DS,
            $aeSettings->getFolderDocs(true).
            ltrim($params['filename'], DS)
        );

        $folder = dirname($fullname);

        // $fullname will be something like
        // c:\site\docs\docs\CMS\Joomla\JUGWallonie\index.html
        //
        // ==> the objectifve is to create an index.html rendering with the list of .md Files
        // found in the c:\site\docs\docs\CMS\Joomla\JUGWallonie\ folder.

        if (!$aeFiles->folderExists($folder)) {
            $aeFunctions->folderNotFound($folder);
        }

        $files = glob(mb_convert_encoding($folder, "ISO-8859-1", "UTF-8").DS."*.md");

        $arr = array();

        foreach ($files as $file) {
            $markdown = file_get_contents($file);

            $arr[] = array(
                'fmtime' => filectime($file),
                'time' => date("Y-m-d", filectime($file)),
                'file' => utf8_encode($aeFiles->removeExtension(basename($file))),
                'text' => $aeMD->getHeadingText($markdown)
            );
        }

        // Sort the list of files, descending (the most recent file first)
        usort($arr, function ($a, $b) {
            return strcmp($b['fmtime'], $a['fmtime']);
        });

        // Retrieve the settings for the boostrap list items
        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'bootstrap');
        $icon = $arrSettings['bullet'] ?? 'check';
        $extra = $arrSettings['extra_attribute'] ?? '';

        // Build the list
        $list = '<ul class="fa-ul">';

        foreach ($arr as $entry) {
            $list .= '<li><i class="fa-li fa fa-'.$icon.'" '.$extra.'></i><span class="index_date">'.$entry['time'].' - </span><a href="'.$entry['file'].'" class="index_file">'.$entry['text'].'</a> '.
            '(<a href="'.$entry['file'].'.html" class="index_file">html</a>)</li>';
        }

        $list .= '</ul>';

        // Read the template
        $template = file_get_contents($aeSettings->getTemplateFile('index'));

        // And generate the output : template + list of files
        $html = str_replace('%CONTENT%', $list, $template);

        $html = $aeHTML->replaceVariables($html, '', null);

        // --------------------------------
        // Call content plugins
        $aeEvents->loadPlugins('content', 'html');
        $args = array(&$html);
        $aeEvents->trigger('render.index', $args);
        $html = $args[0];
        // --------------------------------

        return $html;
    }
}
