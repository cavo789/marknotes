<?php

/**
 * Initialization of the Debug class; file included in the
 * index.php and router.php file, during the loading time of the
 * application so the debugging functionnality is ready as
 * soon as possible.
 */

namespace MarkNotes\Includes;

defined('_MARKNOTES') or die('No direct access allowed');

use \MarkNotes\Autoload;

define('ERROR_PHP_VERSION', 5001);
define('ERROR_HTACCESS', 5002);

class Initialize
{
	// Where marknotes source files are stored
	// Can be different of the website root folder if the
	// marknotes folder is a symbolic link
	private static $appRootFolder = '';

	// Website root folder (f.i. c:\sites\notes)
	private static $webRootFolder = '';

	public function setDocFolder() : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the doc folder
		$docs = $aeSettings->getFolderDocs(true);

		// Check if notes should be stored in the cloud (Dropbox, ...)
		$arrSettings = $aeSettings->getPlugins('/cloud',
			array('platform'=>'', 'enabled'=>0)
		);

		// Does the cloud setting enabled ?
		$enabled = $arrSettings['enabled']??0;

		// If yes, read the name of the "platform" (Dropbox, Amazon, ...)
		// If no, initialize to an empty string
		$platform = ($enabled ? ($arrSettings['platform']??'') : '');

		$aeFiles->setDocFolder($arrSettings, $docs);
		$aeFolders->setDocFolder($arrSettings, $docs);

		return true;
	}

	/**
	 * Website root folder
	 */
	public function getWebRoot() : string
	{
		return self::$webRootFolder;
	}

	/**
	 * Display an error message and die() since the installation
	 * isn't correct
	 */
	private static function die(int $code)
	{
		$fname = '';

		switch ($code) {
			case ERROR_PHP_VERSION:
				$fname = 'error_php_version';
				break;
			case ERROR_HTACCESS:
				$fname = 'error_htaccess';
				break;
		}

		if ($fname!=='') {
			$fname = self::$appRootFolder.'marknotes/errors/'.$fname.'.html';

			if (is_file($fname)) {
				if (!defined('GITHUB_REPO')) {
					require_once(self::$appRootFolder.'marknotes/includes/constants.php');
				}

				$content = str_replace('%ROOT%', self::$appRootFolder, file_get_contents($fname));
				$content = str_replace('%GITHUB%', GITHUB_REPO, $content);
			} else {
				echo '<h3><strong>'.$fname.' not found</strong></h3>';
			}
		} else {
			$content = '<p><strong>Marknotes - Unknown error code '.
			'['.$code.']</strong></p>';
		} // if ($fname!=='')

			header('Content-Transfer-Encoding: ascii');
			header('Content-Type: text/html; charset=utf-8');

			die($content);
	}

	/**
	 * In order to make SEF URLs working, marknotes need a
	 * .htaccess file
	 * Check the presence of the .htaccess file; if not
	 * present, create it by getting a copy of htaccess.txt
	 */
	private static function createHTAccess() : bool
	{
		$fname = self::$webRootFolder.'.htaccess';

		if (!is_file($fname)) {
			if (is_writable(dirname($fname))) {
				$fSource = self::$webRootFolder.'htaccess.txt';
				if (!is_file($fSource)) {
					$fSource = self::$appRootFolder.'htaccess.txt';
				}

				if (is_file($fSource)) {
					copy($fSource, $fname);
					chmod($fname, CHMOD_FILE);
				}
			} else {
				self::die(ERROR_HTACCESS);
			}
		}

		return true;
	}

	/**
	 * Initialize the debug class
	 */
	private static function initDebug(string $fname) : bool
	{

		/*<!-- build:debug -->*/
		$aeDebug = \MarkNotes\Debug::getInstance();

		$aeJSON = \MarkNotes\JSON::getInstance();
		$json = $aeJSON->json_decode($fname, true);

		$arr = $json['debug'] ?? array('enabled'=>0,'development'=>0);

		// Get the debug node of the settings.json file and if not present,
		// create a "fake" ArrayAccess
		//
		// The node should be something like this :
		//
		//		"debug": {
		//			"enabled": 1,
		//			"development": 1,
		//			"logfile": {
		//				"template": "[%datetime%] [%level_name%] %message% %context%"
		//			}
		//		}

		$debug = boolval($arr['enabled'] ?? 0);

		if (isset($arr['logfile'])) {
			if (isset($arr['logfile']['template'])) {
				$aeDebug->setTemplate(trim($arr['logfile']['template']));
			}
		}

		if (isset($arr['output'])) {
			// Output defines which type of informations should be logged.
			// By default, the output node is defined like this :
			//
			//	 	"output": {
			//			"debug": 1,
			//			"info": 1,
			//			"notice": 1,
			//			"warning": 1,
			//			"error": 1,
			//			"critical": 1,
			//			"alert": 1,
			//			"emergency": 1
			//		}

			$aeDebug->setOutput($arr['output']);
		}

		if (isset($json['timezone'])) {
			$aeDebug->setTimezone($json['timezone']);
		}

		if ($debug) {
			$aeDebug->enable();
			$aeDebug->setDevMode(boolval($arr['development'] ?? 0));
		}

		/*<!-- endbuild -->*/

		return true;
	}

	/**
	 * Run initialization code like systems check
	 * Initialize the Marknotes\Debug class
	 */
	public static function init(string $webRoot = '')
	{
		$bReturn = false;

		include_once 'constants.php';

		// Application root folder.
		self::$appRootFolder = rtrim(dirname(dirname(__DIR__)), DS).DS;
		self::$appRootFolder = str_replace('/', DS, self::$appRootFolder);

		if ($webRoot=='') {
			self::$webRootFolder=trim(dirname($_SERVER['SCRIPT_FILENAME']), DS);
			self::$webRootFolder=str_replace('/', DS, self::$webRootFolder).DS;
		} else {
			self::$webRootFolder=rtrim($webRoot, DS).DS;
		}

		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			self::$webRootFolder = DS.ltrim(self::$webRootFolder, DS);
			self::$appRootFolder = DS.ltrim(self::$appRootFolder, DS);
		}

		if (version_compare(phpversion(), '7.0.0', '<')) {
			self::die(ERROR_PHP_VERSION);
		} else {
			self::createHTAccess();

			// Load third parties

			// ----------------------------------------------------
			// If Parsedown and ParsedownExtra aren't loaded *before*
			// ParsedownCheckbox, autoload will fail. Is it possible
			// to *prioritize* libraries in the autoload.php file
			// generated by Composer ? No idea...
			//include_once self::$webRootFolder.'/libs/erusev/parsedown/Parsedown.php';
			//include_once self::$webRootFolder.'/libs/erusev/parsedown-extra/ParsedownExtra.php';
			// ----------------------------------------------------

			include_once self::$webRootFolder.'/libs/autoload.php';

			// Load marknotes's autoloader
			include_once self::$webRootFolder.'/marknotes/includes/autoload.php';
			\MarkNotes\Autoload::register();

			/*<!-- build:debug -->*/
			// First the settings.json.dist if present
			if (is_file($fname = dirname(self::$webRootFolder).DS.'settings.json.dist')) {
				self::initDebug($fname);
			}

			// Then the settings.json if present too
			if (is_file($fname = dirname(self::$webRootFolder).DS.'settings.json')) {
				self::initDebug($fname);
			}
			/*<!-- endbuild -->*/

			// Initialize a few classes

			$aeFolders = \MarkNotes\Folders::getInstance(self::$webRootFolder);

			$aeFiles = \MarkNotes\Files::getInstance(self::$webRootFolder);

			$aeFunctions = \MarkNotes\Functions::getInstance();

			// Retrieve the note to process if any
			$filename = rawurldecode($aeFunctions->getParam('file', 'string', '', false));
			$filename = rtrim($filename, DS);
			$filename = str_replace('/', DS, $filename);

			$params = array('filename'=>$filename);
			$aeSettings = \MarkNotes\Settings::getInstance(self::$webRootFolder, $params);

			$aeCache = \MarkNotes\Cache::getInstance(self::$webRootFolder);

			$bReturn = true;
		} // if (version_compare(phpversion(), '7.0.0', '<'))

		return $bReturn;
	}
}
