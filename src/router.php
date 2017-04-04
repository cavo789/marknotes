<?php
/* REQUIRES PHP 7.x AT LEAST */

/**
 * Author : AVONTURE Christophe - https://www.aesecure.com
 *
 * Documentation : https://github.com/cavo789/marknotes/wiki
 * Demo : https://marknotes.cavo789.com
 * History : https://github.com/cavo789/marknotes/blob/master/changelog.md
 */

define('_MARKNOTES', 1);

include_once 'marknotes/constants.php';

include_once 'autoload.php';
use \MarkNotes\Autoload;

\MarkNotes\Autoload::register();

/*<!-- build:debug -->*/
$aeDebug=\MarkNotes\Debug::getInstance();
/*<!-- endbuild -->*/

// Application root folder.
$folder=str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME']));
$folder=rtrim($folder, DS).DS;

$aeFiles = \MarkNotes\Files::getInstance();
$aeFunctions = \MarkNotes\Functions::getInstance();

$filename=utf8_decode($aeFunctions->getParam('file', 'string', '', false));

$params=array('filename'=>$filename);
$aeSettings = \MarkNotes\Settings::getInstance($folder, $params);

// Check the optional format parameter.  If equal to 'slides', the task will be 'slideshow', 'display' otherwise
$format=$aeFunctions->getParam('format', 'string', 'html', false, 8);

// Only these format are recognized.  Default : html
if (!(in_array($format, array('htm','html','pdf','slides')))) {
    $format='html';
}

$params=array();

if ($filename!=='') {
    $fileMD='';

    if (in_array($filename, array('timeline.html', 'sitemap.xml'))) {
        // Specific files

        switch ($filename) {
            case 'timeline.html':
                $task='timeline';
                break;

            case 'sitemap.xml':
                $task='sitemap';
                break;
        } // switch

        $filename='';
    } else {
        $layout=$aeFunctions->getParam('layout', 'string', '', false, 10);
        if (!(in_array($layout, array('remark','reveal')))) {
            $layout='';
        }

        if ($layout!=='') {
            $params['layout']=$layout;
        }

        switch ($format) {
            case 'pdf':
                $task='pdf';
                break;

            case 'slides':
                // Check on the URL if the user has forced a layout i.e. "remark" or "reveal", the supported slideshow framework

                $task='slideshow';

                break;

            default:                  // htm or html
                $task='display';
                if (!isset($params['layout'])) {
                    $params['layout']='html';
                }
                break;
        } // switch

        // Get the absolute folder name where the web application resides (f.i. c:\websites\marknotes\)
        $webRoot=$aeSettings->getFolderWebRoot(true);

        // Build the full filename
        $filename=str_replace('/', DIRECTORY_SEPARATOR, $filename);

        $fileMD=$aeFiles->removeExtension($filename).'.md';

        if (!$aeFiles->fileExists($webRoot.$fileMD)) {
            $fileMD=utf8_decode($fileMD);
        }

        if (!$aeFiles->fileExists($webRoot.$fileMD)) {
            header("HTTP/1.0 404 Not Found");
            die('File '. $aeFiles->sanitizeFileName($fileMD).' not found');
        }
    } // if (in_array($filename, array('timeline.html', 'sitemap.xml')))


    // Create an instance of the class and initialize the rootFolder variable (type string)

    $aeSMarkDown = new \MarkNotes\Markdown();

    // $fileMD filename should be relative ()
    $aeSMarkDown->process($task, $fileMD, $params);
    unset($aeSMarkDown);
}
