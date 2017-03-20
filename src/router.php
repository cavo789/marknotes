<?php

require_once __DIR__.'/classes/files.php';
require_once __DIR__.'/classes/functions.php';

$filename=\AeSecure\Functions::getParam('file', 'string', '', false);

// Check the optional format parameter.  If equal to 'slides', the task will be 'slideshow', 'display' otherwise
$format=\AeSecure\Functions::getParam('format', 'string', 'html', false, 8);

if (!(in_array($format, array('html','slides')))) $format='html';

if ($filename!=='') {

    require_once __DIR__.'/classes/markdown.php';

    $task=($format==='slides') ? 'slideshow' : 'display';

    $filename = str_replace('/', DIRECTORY_SEPARATOR, str_replace('docs/', '', \AeSecure\Files::removeExtension($filename))).'.md';

    // Create an instance of the class and initialize the rootFolder variable (type string)
    $aeSMarkDown = new \AeSecure\Markdown();
    $aeSMarkDown->process($task, $filename);
    unset($aeSMarkDown);

}
