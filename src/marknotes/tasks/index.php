<?php

/**
* Return a dynamic index.html page
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

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -5) != '.html') {
            $params['filename'] .= '.html';
        }

        $fullname = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $aeSettings->getFolderWebRoot().
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

        $files = glob($folder.DS."*.md");

        $arr = array();

        $docs = $aeSettings->getFolderDocs(true);

        foreach ($files as $file) {
            $markdown = file_get_contents($file);

            $arr[] = array(
                'fmtime' => filectime($file),
                'time' => date("Y-m-d", filectime($file)),
                'file' => $aeFiles->removeExtension(basename($file)),
                'text' => $aeMD->getHeadingText($markdown)
            );
        }

        // Sort the list of files,  descending
        usort($arr, function ($a, $b) {
            return strcmp($b['fmtime'], $a['fmtime']);
        });

        // Build the list

        // Read the template
        $template = file_get_contents($aeSettings->getTemplateFile('index'));

        $list = '<ul class="fa-ul">';

        foreach ($arr as $entry) {
            $list .= '<li cass="fa fa-check"><span class="index_date">'.$entry['time'].' - </span><a href="'.$entry['file'].'" class="index_file">'.$entry['text'].'</a></li>';
        }

        $list .= '</ul>';

        $html = str_replace('%CONTENT%', $list, $template);

        //}
        $html = $aeHTML->replaceVariables($html, '', null);


/*
        // --------------------------------
        // Call content plugins
        $aeEvents->loadPlugins('content', 'html');
        $args = array(&$html);
        $aeEvents->trigger('render.index', $args);
        $html = $args[0];
        // --------------------------------
*/
        return $html;
    }
}
