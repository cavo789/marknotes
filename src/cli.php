<?php

define('_MARKNOTES', 1);

include_once 'marknotes/constants.php';

include_once 'autoload.php';
use \MarkNotes\Autoload;

\MarkNotes\Autoload::register();

$aeDebug=\MarkNotes\Debug::getInstance();
$aeFiles=\MarkNotes\Files::getInstance();
$aeFunctions=\MarkNotes\Functions::getInstance();
$aeSettings=\MarkNotes\Settings::getInstance();

// No timeout please
set_time_limit(0);
$webRoot=getcwd();

parse_str(implode('&', array_slice($argv, 1)), $_GET);

$task=$_GET['task'];
$filename=json_decode(urldecode(base64_decode($_GET['param'])));

$aeTask=\MarkNotes\Tasks\Slideshow::getInstance();

$params=array();
$params['filename']=$filename;
$params['layout']='reveal';

$tmpHTML = $aeFiles->replaceExtension($webRoot.DS.$aeSettings->getFolderDocs(true).$filename, 'html');
$pdfFinal = $aeFiles->replaceExtension($tmpHTML, 'pdf');

$content=$aeTask->run($params);
file_put_contents($tmpHTML, $content);

//echo '***'.$pdfFinal.PHP_EOL;
