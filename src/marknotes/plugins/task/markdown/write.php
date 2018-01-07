<?php

namespace MarkNotes\Plugins\Markdown;

use \Symfony\Component\Yaml\Yaml;

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

			$content = trim($params['markdown']);

			// Add the YAML block if allowed by settings.json
			$aeEvents = \MarkNotes\Events::getInstance();
			$params['markdown'] = $content;
			$params['yaml'] = '';
			$aeEvents->loadPlugins('markdown.yaml');
			$args = array(&$params);
			$aeEvents->trigger('markdown.yaml::markdown.read', $args);
			$html = $args[0]['markdown'];

			// Check if there is a YAML header and if so,
			// add in back in the .md file
			if ($params['yaml']!=='') {
				$yaml = $aeSession->get('yaml');
				$content = "---".PHP_EOL.$yaml."---".PHP_EOL.
					PHP_EOL.$content;
			}

			// And write the file
			$aeFiles->rewrite($filename, $content);

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
