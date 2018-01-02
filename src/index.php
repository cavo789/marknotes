<?php
/**
* Author : AVONTURE Christophe - https://www.aesecure.com
*
* Documentation : https://github.com/cavo789/marknotes/wiki
* Demo : https://marknotes.cavo789.com
* History : https://github.com/cavo789/marknotes/blob/master/changelog.md
*/
namespace MarkNotes;
define('_MARKNOTES', 1);

// As fast as possible, enable debugging mode if
// settings.json->debug mode is enabled
include_once 'marknotes/includes/debug_show_errors.php';

include_once 'marknotes/includes/initialize.php';
$class = new Includes\Initialize();

$bReturn = $class->init();

// Remember the website root folder
$root = $class->getWebRoot();

unset($class);

if ($bReturn) {

	error_reporting(E_ALL);

	$aeFunctions = \MarkNotes\Functions::getInstance($root);

	if (isset($_GET['file'])) {
		$filename = rawurldecode($aeFunctions->getParam('file', 'string', '', false));
	} else {
		$tmp=urldecode($aeFunctions->getParam('param', 'string', '', true));
		$filename=((json_decode($tmp)!='')?json_decode($tmp):$tmp);
	}

	$filename = rtrim($filename, DS);
	$filename = str_replace('/', DS, $filename);

	$task = rawurldecode($aeFunctions->getParam('task', 'string', '', false));

	$params = array('filename' => $filename);
	$aeSettings = \MarkNotes\Settings::getInstance($root, $params);

	/*<!-- build:debug -->*/
	if ($aeSettings->getDebugMode()) {
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeDebug->log("*** START of marknotes - index.php ***","debug");
	}
	/*<!-- endbuild -->*/

	$aeSession = \MarkNotes\Session::getInstance($root);

	// Check if notes should be stored in the cloud (Dropbox, ...)
	$arrSettings = $aeSettings->getPlugins('/cloud', array('platform'=>''));
	$platform = $arrSettings['platform']??'';
	if(trim($platform)!=='') {
		$enabled = $arrSettings['enabled']??0;
		if (boolval($enabled)) {
			// Get the doc folder
			$docs = $aeSettings->getFolderDocs(true);
			// Initialize the cloud
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFolders = \MarkNotes\Folders::getInstance();
			$aeFiles->setCloud($arrSettings, $docs);
			$aeFolders->setCloud($arrSettings, $docs);
		}
	} // if(trim($platform)!=='')

	$aeFunctions = \MarkNotes\Functions::getInstance();
	$aeJSON = \MarkNotes\JSON::getInstance();

	$aeFunctions = \MarkNotes\Functions::getInstance();
	$task = $aeFunctions->getParam('task', 'string', 'main', false);

	$aeMarkDown = new \MarkNotes\Markdown();
	$aeMarkDown->process($task);
	unset($aeMarkDown);

	/*<!-- build:debug -->*/
	if ($aeSettings->getDebugMode()) {
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeDebug->logEnd();
	}
	/*<!-- endbuild -->*/

} // if ($bReturn)
