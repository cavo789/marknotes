<?php
/* REQUIRES PHP 7.x AT LEAST */
/**
 *
 * @link https://github.com/nette/tracy for PHP errors
 * @link https://github.com/Seldaek/monolog for debug output
 */
namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;

class Debug
{
	protected static $hInstance = null;

	private static $startedAt = null;
	private static $bEnabled = false;
	private static $logger = null;
	private static $arrLevels = array('debug','info','notice','warning','error','critical','alert','emergency');

	private static $bInit = false;

	private static $lastmsg='';

	private static $root = '';
	private static $sTemplate = DEBUG_TEMPLATE;
	private static $sTimezone = DEFAULT_TIMEZONE;
	private static $sDebugFileName = DEBUG_LOG_NAME;
	private static $bDevMode = false;

	public function __construct(string $root = '')
	{
		static::$startedAt = microtime(true);

		// Root folder
		if (trim($root)=='') {
			self::$root = rtrim(dirname($_SERVER['SCRIPT_FILENAME']), DS).DS;
		} else {
			self::$root = $root;
		}

		self::$root = str_replace('/', DS, self::$root);

		static::$bEnabled = false;
		self::$sTemplate = DEBUG_TEMPLATE;
		self::$sTimezone = DEFAULT_TIMEZONE;

		$aeFiles = \MarkNotes\Files::getInstance(self::$root);
		$aeFolders = \MarkNotes\Folders::getInstance(self::$root);

		// full path for the debug.log file : in the /tmp folder
		if (!$aeFolders->exists(self::$root.'tmp'.DS)) {
			$aeFolders->create(self::$root.'tmp'.DS);
		}

		self::$sDebugFileName = self::$root.'tmp'.DS.DEBUG_LOG_NAME;

		// Don't keep previous run
		if ($aeFiles->exists(self::$sDebugFileName)) {
			try {

				$time = $aeFiles->timestamp(self::$sDebugFileName, true);

				if (time()-$time > 120) {
					// the debug.log file is older than 2 minutes
					// (based on the lastmodification date/time)
					// rewrite the file so we can start with a
					// fresh file each 2 minutes
					@$aeFiles->delete(self::$sDebugFileName);
					$aeFiles->create(self::$sDebugFileName, '');
				}
			} catch (Exception $e) {
			}
		}

		self::$bDevMode = false;

		return true;
	}

	public static function getInstance(string $root = '')
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Debug($root);
		}
		return self::$hInstance;
	}

	/**
	 * Set the developper mode or not
	 */
	public function setDevMode(bool $bOnOff = false)
	{
		self::$bDevMode = $bOnOff;

		/*<!-- build:debug -->*/
		// Load the Tracy toolbar only in DEV mode
		if (self::$bDevMode) {
			// Load tracy if found otherwise check php-error
			$lib = self::$root.'libs/tracy/tracy';
			if (is_dir($lib)) {
				// @https://github.com/nette/tracy
				\Tracy\Debugger::enable();
				\Tracy\Debugger::$showBar = $bOnOff;
				\Tracy\Debugger::$strictMode = $bOnOff;
				\Tracy\Debugger::$showLocation = $bOnOff;
			} else {
				// include php_error library to make life easier
				// @link : https://github.com/JosephLenton/PHP-Error
				$lib = self::$root.'libs/php_error/php_error.php';

				$aeFiles = \MarkNotes\Files::getInstance();
				if ($aeFiles->exists($lib)) {
					$aeFunctions = \MarkNotes\Functions::getInstance();

					// Seems to not work correctly with ajax; the return
					// JSON isn't correctly understand by JS
					$options = array(
						// Don't enable ajax is not ajax call
						'catch_ajax_errors' => $aeFunctions->isAjaxRequest(),
						// Don't allow to modify sources from php-error
						'enable_saving' => 0,
						// Capture everything
						'error_reporting_on' => $bOnOff
					);

					include_once $lib;
					\php_error\reportErrors($options);
				} // if ($aeFiles->exists($lib))
			}
		}
		/*<!-- endbuild -->*/

		return true;

	}

	public function getDevMode() : bool
	{
		return self::$bDevMode ? true : false;
	}

	/**
	 * By default template is equal to
	 *	 [%datetime%] [%level_name%] %message% %context%
	 * (as defined in constants.php, constant : DEBUG_TEMPLATE
	 * But can be override in settings.json debug->logfile->template
	 */
	public function setTemplate(string $template = DEBUG_TEMPLATE)
	{
		self::$sTemplate = $template;
	}

	/**
	 * Output defines which type of informations should be logged.
	 * By default, the output node is defined like this :
	 *
	 *		"output": {
	 *			"debug": 1,
	 *			"info": 1,
	 *			"notice": 1,
	 *			"warning": 1,
	 *			"error": 1,
	 *			 "critical": 1,
	 *			 "alert": 1,
	 *			 "emergency": 1
	 *		}
	 *
	 * Process the $arr array (defined in the settings.jsom) and remove
	 * from the intern $arrLevels all options set to 0.
	 */
	public function setOutput(array $arr) : bool
	{
		$sDisabled='';

		foreach ($arr as $key => $value) {
			if ($value==0) {
				$sDisabled.=$key.';';
			}
		}

		$sDisabled=rtrim($sDisabled, ';');

		if ($sDisabled!=='') {
			// At least one level has been disabled, f.i., the
			// settings.json has been defined like this :
			//
			//	"output": {
			//		"debug": 0
			//	}
			//
			// This means : report everything (info, warning,
			// error, ...) but not debug info.

			$arrDisabled=explode(';', $sDisabled);

			$tmp = array();

			foreach (self::$arrLevels as $key) {
				if (!in_array($key, $arrDisabled)) {
					$tmp[]=$key;
				}
			}

			// Set the new array
			self::$arrLevels=$tmp;
		} // if ($sDisabled!=='')

		return true;
	}

	/**
	 * Set the timezone
	 */
	public function setTimezone(string $timezone = DEFAULT_TIMEZONE)
	{
		self::$sTimezone = $timezone;
	}

	/**
	 * Capture all PHP errors and allow to display them on screen
	 */
	private static function enablePHPErrors() : bool
	{
		ini_set("display_errors", "1");
		ini_set("display_startup_errors", "1");
		ini_set("html_errors", "1");
		ini_set("docref_root", "http://www.php.net/");

		// Log php errors in the temporary folder
		ini_set('error_log', self::$root.'tmp'.DS.'php_errors.log');

		ini_set(
			"error_prepend_string",
			"<div style='color:black;'."
			. "'font-family:verdana;border:1px solid red; padding:5px;'>"
		);

		ini_set("error_append_string", "</div>");

		error_reporting(E_ALL);

		return true;
	}

	/**
	 * Hide PHP errors
	 */
	private static function disablePHPErrors() : bool
	{
		error_reporting(0);  // E_NONE
		return true;
	}

	/**
	 * Load the Monolog Open Source library
	 *
	 * @link https://github.com/Seldaek/monolog
	 */
	private static function loadMonolog() : bool
	{
		$bReturn = false;

		/*<!-- build:debug -->*/
		$lib = self::$root.'libs/monolog/monolog/src/';

		$aeFolders = \MarkNotes\Folders::getInstance();
		if ($aeFolders->exists($lib)) {
			$formatter = new LineFormatter(self::$sTemplate."\n", "Y-m-d H:i:s");

			// \Monolog\Logger::DEBUG =  The minimum logging level
			// at which this handler will be triggered (debug
			// is the lowest)
			$streamHandler = new StreamHandler(self::$sDebugFileName, \Monolog\Logger::DEBUG);

			$streamHandler->setFormatter($formatter);

			self::$logger = new \Monolog\Logger('marknotes');
			self::$logger->pushHandler($streamHandler);
			self::$logger::setTimezone(new \DateTimeZone(self::$sTimezone));

			$bReturn = true;
		} // if ($aeFolders->exists($lib))
		/*<!-- endbuild -->*/

		return $bReturn;
	}

	/**
	 * Enable the debugger; initialize variables and objects
	 */
	public function enable(bool $bOnOff = false) : bool
	{
		/*<!-- build:debug -->*/

		if (static::$bEnabled) {
			// The debug mode is currently active and
			// a code is asking to disable it
			if (!$bOnOff) {
				self::log('*** DEBUG MODE IS DISABLING ***', 'debug', 3);
			}
		}

		static::$bEnabled = $bOnOff;

		if ($bOnOff) {
			self::enablePHPErrors();

			self::loadMonolog();

			if (!static::$bInit) {
				self::log("*** START of marknotes ***", "debug", 3);
				static::$bInit=true;
			}
		} else { // if ($bOnOff)
			self::disablePHPErrors();
		}
		/*<!-- endbuild -->*/

		return true;
	}

	/**
	 * Show the END of ...
	 */
	public static function logEnd() : bool
	{
		self::log("*** END of marknotes ***", "debug", 3);

		// Add the elapsed time in seconds in the log file
		$timeTaken =  microtime(true) - static::$startedAt;
		self::log("*** Time taken : ".$timeTaken." seconds ***", "debug");

		return true;
	}

	/**
	 * Add an entry in the /tmp/debug.log file
	 */
	public static function log(
		string $msg = '',
		string $method = 'debug',
		int $deep = 3,
		bool $shortFilename = true
	) : bool {

		/*<!-- build:debug -->*/

		// Show the same message only once;
		if (self::$lastmsg !== trim($msg)) {
			self::$lastmsg=trim($msg);

			if (self::$logger !== null) {
				// Try to keep the log file readable : remove the
				// parent path if present so filenames will be relative
				if ($shortFilename) {
					$msg = str_ireplace(self::$root, '', $msg);
				}

				if (!in_array($method, array('debug','info','notice','warning','error','critical','alert','emergency'))) {
					$method = 'debug';
				}

				if (in_array($method, self::$arrLevels)) {
					$trace = debug_backtrace();

					$class = ($trace[1]['class'] ?? '').'::'.($trace[1]['function'] ?? '');

					$context[]['caller'] = $class.' line '.$trace[0]['line'];

					if ($deep > 1) {
						// Add the previous caller
						$file = str_ireplace(self::$root, '', $trace[1]['file'] ?? '');
						$parent = ($trace[2]['class'] ?? $file).'::'.($trace[2]['function'] ?? '');
						$context[]['caller'] = $parent.' line '.($trace[1]['line'] ?? '');
					}

					if ($deep > 2) {
						for ($i=2; $i<$deep; $i++) {
							if (isset($trace[$i+1]['class'])) {
								// Add the previous caller
								$file = str_ireplace(self::$root, '', $trace[$i]['file']);

								$parent = ($trace[$i+1]['class'] ?? $file).'::'.($trace[$i+1]['function'] ?? '');
								$context[]['caller'] = $parent.' line '.$trace[$i]['line'];
							}
						}
					}

					self::$logger->$method($class.' - '.$msg, $context);
				} else { // if (in_array($method, self::$arrLevels))
					//
					//echo "Debug info of type [".$method."] have been disabled in".
					//	"settings.json, no output in the logfile for them<br/>";
				}
			} // if (self::$logger !== null)
		}
		/*<!-- endbuild -->*/

		return true;
	}

	public function here($msg = null, $deep = 3, $return = false) : string
	{

		/*<!-- build:debug -->*/
		$pos = 0;
		if ($deep < 1) {
			$deep = 1;
		}

		$debugTrace = debug_backtrace();
		$class = '';
		$file = '';
		$line = '';
		$func = '';
		$txt = '';

		$previous = '';
		for ($i = 0; $i < $deep; $i++) {
			if (isset($debugTrace[$pos + $i])) {
				$file = isset($debugTrace[$pos + $i]['file']) ? $debugTrace[$pos + $i]['file'] : '';
				$line = isset($debugTrace[$pos + $i]['line']) ? $debugTrace[$pos + $i]['line'] : '';
			}
			if (isset($debugTrace[$pos + $i + 1])) {
				$class = isset($debugTrace[$pos + $i + 1]['class'])
					? $debugTrace[$pos + $i + 1]['class'].'::'
					: '';
				$func = isset($debugTrace[$pos + $i + 1]['function'])
					? $debugTrace[$pos + $i + 1]['function'].'()'
					: '';
			}
			if (($line != '') && ($line !== $previous)) {
				$previous = $line;
				$txt .= ($deep > 1?'<li>':'').$class.$func.' in&nbsp;'.$file.' line&nbsp;'.$line.($deep > 1?'</li>':'');
			}
		} // for

		$txt = '<pre style="background-color:yellow;padding:10px">'.__METHOD__.
			' called by '.($deep > 1?'<ol>':'').$txt.($deep > 1?'</ol>':'').
			($msg != null?'<div style="padding:10px;border:1px dotted;">'.print_r($msg, true).'</div>':'').
			'</pre>';

		echo $txt;
		/*<!-- endbuild -->*/

		return true;
	}
}
