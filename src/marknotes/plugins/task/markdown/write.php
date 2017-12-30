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

			// Check if there is a YAML header and if so,
			// add in back in the .md file
			$yaml=trim($aeSession->get('yaml', ''), "'");
			$yaml=trim($yaml, '"');

			if ($yaml!=='') {
				$lib=$aeSettings->getFolderLibs()."symfony/yaml/Yaml.php";

				if (is_file($lib)) {
					include_once $lib;

					// Yaml::dump will add double-quotes so remove them
					$content=
						"---".PHP_EOL.
						str_replace('\\n', PHP_EOL, trim(Yaml::dump($yaml), '"')).PHP_EOL.
						"---".PHP_EOL.PHP_EOL.
						$content;
				}
			}

			// And write the file
			$aeFiles = \MarkNotes\Files::getInstance();
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
