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

// Load third parties
include_once 'libs/autoload.php';

// Load Marknotes classes
include_once 'autoload.php';
use \MarkNotes\Autoload;

if (version_compare(phpversion(), '7.0.0', '<')) {
    $root = dirname($_SERVER['SCRIPT_NAME']);
    $content = str_replace('%ROOT%', $root, file_get_contents(__DIR__.'/error_php.html'));
    echo $content;
} else {
    \MarkNotes\Autoload::register();

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
    //if (!(in_array($format, array('docx','epub','htm','html','pdf','slides', 'txt')))) {
    //    $format = 'html';
    //}

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

            // Get the absolute folder name where the web application resides (f.i. c:\websites\marknotes\)
            $webRoot = $aeSettings->getFolderWebRoot(true);

            // Build the full filename
            $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);

            $fileMD = $aeFiles->removeExtension($filename).'.md';

            if (!$aeFiles->fileExists($webRoot.$fileMD)) {
                $fileMD = utf8_decode($fileMD);
            }

            if (!$aeFiles->fileExists($webRoot.$fileMD)) {
                $aeFunctions = \MarkNotes\Functions::getInstance();
                $aeFunctions->fileNotFound($aeFiles->sanitizeFileName($fileMD));
            }

            // Get the extension (f.i. "pdf")
            // In case of double extension (f.i. "reveal.pdf"), the first part will
            // be understand as a layout ("reveal")

            $layout = '';
            $fileExt = $aeFiles->getExtension($filename);
            if (strpos($fileExt, ".") !== false) {
                $arr = explode(".", $fileExt);
                $layout = $arr[0];
                $format = $arr[1];
            }

            if ($layout !== '') {
                $params['layout'] = $layout;
            }
        } // if (in_array($filename, array('timeline.html', 'sitemap.xml')))

        // Create an instance of the class and initialize the rootFolder variable (type string)

        $aeSMarkDown = new \MarkNotes\Markdown();

        // $fileMD filename should be relative ()
        $aeSMarkDown->process($format, $fileMD, $params);
        unset($aeSMarkDown);
    }
}
