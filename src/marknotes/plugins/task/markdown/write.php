<?php

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Write extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.markdown.write';
	protected static $json_options = '';

	public static function run(&$params = null) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Retrieve the filename from the session
		$filename = $aeSession->get('filename', '');

		// Be sure to have the .md extension
		$filename = $aeFiles->removeExtension($filename).'.md';

		if ($filename !== '') {
			// Get the absolute filename on the disk
			if (!$aeFunctions->startsWith($filename, $aeSettings->getFolderDocs(true))) {
				$filename = $aeSettings->getFolderDocs(true).$filename;
			}

			// And write the file
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFiles->rewriteFile($filename, trim($params['markdown']));

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('Rewrite DONE '.$filename, 'debug');
			}
			/*<!-- endbuild -->*/
		} else {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->here('Event markdown.write - Session invalid, no filename found', 5);
			}
			/*<!-- endbuild -->*/
		} // if ($filename !== '')

		return true;
	}
}
