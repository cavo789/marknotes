<?php
/* REQUIRES PHP 7.x AT LEAST */
/**
 * Author : AVONTURE Christophe - https://www.aesecure.com
 *
 * Documentation : https://github.com/cavo789/marknotes/wiki
 * Demo : https://www.marknotes.fr
 * History : https://github.com/cavo789/marknotes/blob/master/changelog.md
 */
namespace MarkNotes;

define('_MARKNOTES', 1);

	// As fast as possible, enable debugging mode if settings.json->debug mode is enabled
	include_once 'marknotes/includes/debug_show_errors.php';

	include_once 'marknotes/includes/initialize.php';

	$class = new Includes\Initialize();
	$bReturn = $class->init();

	// Remember the website root folder
	$root = $class->getWebRoot();

	unset($class);

	if ($bReturn) {
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$filename = rawurldecode($aeFunctions->getParam('file', 'string', '', false));
		$filename = rtrim($filename, DS);
		$filename = str_replace('/', DS, $filename);

		$task = rawurldecode($aeFunctions->getParam('task', 'string', '', false));

		$params = array('filename' => $filename);
		$aeSettings = \MarkNotes\Settings::getInstance($root, $params);

		$aeSession = \MarkNotes\Session::getInstance($root);
		$aeSession->set('filename',$filename);
		$aeSession->set('img_id',0);

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();

		// Initialize the DocFolder filesystem
		$aeInitialize = new Includes\Initialize();
		$aeInitialize->setDocFolder();
		unset($aeInitialize);

		if ($filename !== '') {
			// The filename shouldn't mention the docs folders, just the filename
			// So, $filename should not be docs/markdown.md but only markdown.md because the
			// folder name will be added later on
			$docRoot = $aeSettings->getFolderDocs(false);

			if ($aeFunctions->startsWith($filename, $docRoot)) {
				$filename = substr($filename, strlen($docRoot));
			}
		} // if ($filename !== '')

		$aeMarkDown = new \MarkNotes\Markdown();
		$aeMarkDown->process($task, $filename, $params);
		unset($aeMarkDown);

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->logEnd();
			unset($aeDebug);
		}
		/*<!-- endbuild -->*/
	}
