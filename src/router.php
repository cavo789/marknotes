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

if (version_compare(phpversion(), '7.0.0', '<')) {
    $root = dirname($_SERVER['SCRIPT_NAME']);
    $content = str_replace('%ROOT%', $root, file_get_contents(__DIR__.'/error_php.html'));
    echo $content;
} else {
    \MarkNotes\Autoload::register();

    /*<!-- build:debug -->*/
    $aeDebug = \MarkNotes\Debug::getInstance();
    /*<!-- endbuild -->*/

    // Application root folder.
    $folder = str_replace('/', DS, dirname($_SERVER['SCRIPT_FILENAME']));
    $folder = rtrim($folder, DS).DS;

    $aeFiles = \MarkNotes\Files::getInstance();
    $aeFunctions = \MarkNotes\Functions::getInstance();
    $aeEvents = \MarkNotes\Events::getInstance();

    $filename = rawurldecode($aeFunctions->getParam('file', 'string', '', false));

    $params = array('filename' => $filename);
    $aeSettings = \MarkNotes\Settings::getInstance($folder, $params);

    // Retrieve the asked extension i.e. if the user try to access the /note.html or /note.pdf file,
    // extract the extension (html or pdf)
    $format = '';
    if ($filename !== '') {
        $format = $aeFiles->getExtension($filename);
    }

    // Take a look on the format parameter, if mentionned use that format
    $format = $aeFunctions->getParam('format', 'string', $format, false, 8);

    // Only these format are recognized.  Default : html
    if (!(in_array($format, array('docx','epub','htm','html','pdf','slides', 'txt')))) {
        $format = 'html';
    }
    $params = array();

    if ($filename !== '') {
        $fileMD = '';

        if (in_array($filename, array('timeline.html', 'sitemap.xml'))) {
            // Specific files

            switch ($filename) {
                case 'timeline.html':
                    $task = 'timeline';
                    break;

                case 'sitemap.xml':
                    $task = 'sitemap';
                    break;
            } // switch

            $filename = '';
        } else {
            $layout = $aeFunctions->getParam('layout', 'string', '', false, 10);
            if (!(in_array($layout, array('remark','reveal')))) {
                $layout = '';
            }

            if ($layout !== '') {
                $params['layout'] = $layout;
            }

            switch ($format) {

                case 'docx':
                    $task = 'docx';
                    break;

                case 'epub':
                    $task = 'epub';
                    break;

                case 'pdf':
                    $task = 'pdf';
                    break;

                case 'txt':
                    $task = 'txt';
                    break;

                case 'slides':
                    // Check on the URL if the user has forced a layout i.e. "remark" or "reveal", the supported slideshow framework

                    //$task = 'slideshow';
                    $task = 'slides';

                    break;

                default:                  // htm or html
                    $task = 'display';
                    if (!isset($params['layout'])) {
                        $params['layout'] = 'html';
                    }
                    break;
            } // switch

            // Get the absolute folder name where the web application resides (f.i. c:\websites\marknotes\)
            $webRoot = $aeSettings->getFolderWebRoot(true);

            // Build the full filename
            $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);

            $fileMD = $aeFiles->removeExtension($filename).'.md';

            if (!$aeFiles->fileExists($webRoot.$fileMD)) {
                $fileMD = utf8_decode($fileMD);
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
}
