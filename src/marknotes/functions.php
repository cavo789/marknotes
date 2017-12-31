<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Functions
{
	protected static $hInstance = null;

	public function __construct()
	{
		return true;
	}

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Functions();
		}

		return self::$hInstance;
	}

	public function fileNotFound(string $file = '', bool $die = true) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$msg = $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists');

		header("HTTP/1.0 404 Not Found");

		if ($file !== '') {
			echo(str_replace('$1', '<strong>'.$file.'</strong>', $msg));
		}

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->here('#DebugMode# - File '.$file.' not found', 10);
		}
		/*<!-- endbuild -->*/

		if ($die) {
			die();
		}
	}

	public function folderNotFound(string $folder = '', bool $die = true) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$msg = $aeSettings->getText('folder_not_found', 'The folder [%s] doesn\\&#39;t exists');

		header("HTTP/1.0 404 Not Found");

		if ($folder !== '') {
			echo(str_replace('%s', '<strong>'.$folder.'</strong>', $msg));
		}

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->here('#DebugMode# - Folder '.$folder.' not found', 5);
		}
		/*<!-- endbuild -->*/

		if ($die) {
			die();
		}
	}

	/**
	* Display an error message and, if the debug mode is enabled, gives info about the caller
	*/
	public static function showError(string $code, string $default, bool $bHTML = true) : string
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$caller = '';
		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$caller = ' (called by '.debug_backtrace()[1]['class'].'::'.debug_backtrace()[1]['function'].
			', line '.debug_backtrace()[0]['line'].')';
		}
		/*<!-- endbuild -->*/

		$sReturn = $aeSettings->getText($code, $default).$caller;

		if ($bHTML) {
			$sReturn = '<div class="text-danger">'.$sReturn.'</div>';
		}

		return $sReturn;
	}

	/**
	* Remove any accentuated characters, dot, space, comma, ... and generate
	* a secure string (can be used for an alias or a filename)
	*
	* @link https://github.com/cocur/slugify
	*/
	public static function slugify(string $text) : string
	{

		$aeSettings = \MarkNotes\Settings::getInstance();
		$folder = $aeSettings->getFolderLibs()."slugify/";

		include_once $folder.'RuleProvider/RuleProviderInterface.php';
		include_once $folder.'RuleProvider/DefaultRuleProvider.php';
		include_once $folder.'RuleProvider/FileRuleProvider.php';
		include_once $folder.'SlugifyInterface.php';
		include_once $folder.'Slugify.php';

		$slugify = new \Cocur\Slugify\Slugify();

		// Slugify support different languages (rules);
		// see the $rules array defined in
		// https://github.com/cocur/slugify/blob/master/src/RuleProvider/DefaultRuleProvider.php
		$rule='default';
		if ($aeSettings->getLanguage()=='fr') {
			$rule='french';
		}

		$slugify->activateRuleSet($rule);

		// Be sure to have really nice slugs
		$text = trim(html_entity_decode($text), ' .:,;-?!');

		return $slugify->slugify($text);
	}

	/**
	* Check if a specific function (like exec or shell_execute) is disabled or not
	*/
	public static function ifDisabled(string $fctname) : bool
	{
		$bReturn = false;

		if ($fctname !== '') {
			$disabled = explode(',', ini_get('disable_functions'));
			$bReturn = in_array($fctname, $disabled);
		}

		return $bReturn;
	}

	/**
	* Return the current URL
	*
	* @return type string
	*/
	public static function getCurrentURL() : string
	{

		$ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
		$protocol = 'http';
		// SERVER_PROTOCOL isn't set when the script is fired through a php-cli
		if (isset($_SERVER['SERVER_PROTOCOL'])) {
			$spt = strtolower($_SERVER['SERVER_PROTOCOL']);
			$protocol = substr($spt, 0, strpos($spt, '/')) . (($ssl)?'s':'');
		}

		$port = '80';
		// SERVER_PORT isn't set when the script is fired through a php-cli
		if (isset($_SERVER['SERVER_PORT'])) {
			$port = $_SERVER['SERVER_PORT'];
			$port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':'.$port;
		}

		$host =
		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');

		$host = isset($host) ? rtrim(str_replace(DS, '/', $host), '/') : $_SERVER['SERVER_NAME'].$port;

		$return = $protocol.'://'.$host.dirname($_SERVER['PHP_SELF']).'/';

		return $return;
	}

	/**
	* Safely read posted variables
	*
	* @param  type $name	f.i. "password"
	* @param  type $type	f.i. "string"
	* @param  type $default f.i. "default"
	* @return type
	*/
	public function getParam(
		string $name,
		string $type = 'string',
		$default = '',
		bool $base64 = false,
		int $maxsize = 0
	) {
		$tmp = '';
		$return = $default;

		if ($type=='bool') {
			$type='boolean';
		}

		if (isset($_POST[$name])) {
			if (in_array($type, array('int','integer'))) {
				$return = filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_INT);
			} elseif ($type === 'boolean') {
				// false = 5 characters
				$tmp = trim(substr(filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING), 0, 5));
				$return = (in_array(strtolower($tmp), array('1','on','true')))?true:false;
			} elseif ($type === 'string') {
				$return = trim(filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING));
				if ($base64 === true) {
					$return = base64_decode($return);
				}
				if ($maxsize > 0) {
					$return = substr($return, 0, $maxsize);
				}
			} elseif ($type === 'unsafe') {
				$return = $_POST[$name];
			}
		} else { // if (isset($_POST[$name]))
			if (isset($_GET[$name])) {
				if (in_array($type, array('int','integer'))) {
					$return = filter_input(INPUT_GET, $name, FILTER_SANITIZE_NUMBER_INT);
				} elseif ($type == 'boolean') {
					// false = 5 characters
					$tmp = trim(substr(filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING), 0, 5));
					$return = (in_array(strtolower($tmp), array('1','on','true')))?true:false;
				} elseif ($type === 'string') {
					$return = trim(filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING));
					if ($base64 === true) {
						$return = base64_decode($return);
					}
					if ($maxsize > 0) {
						$return = substr($return, 0, $maxsize);
					}
				} elseif ($type === 'unsafe') {
					$return = $_GET[$name];
				}
			} // if (isset($_GET[$name]))
		} // if (isset($_POST[$name]))

		if ($type == 'boolean') {
			$return = (in_array($return, array('true','on','1'))?true:false);
		}

		return $return;
	}
	/**
	* Add CSS inline and, in case of debugging mode, add information's
	* about the caller
	*/
	public static function addStyleInline(string $css) : string
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$caller = '';
		if ($aeSettings->getDebugMode()) {
			$trace = debug_backtrace();
			$caller = ($trace[1]['class'] ?? '').'::'.($trace[1]['function'] ?? '');
			$caller .= ' line '.$trace[0]['line'];
		}

		if ($aeSettings->getDebugMode()) {
			$css = "\n<!-- Lines below are added by ".$caller."-->\n".
			trim($css, "\n")."\n".
			"<!-- End for ".$caller."-->\n";
		}

		return $css;
	}
	/**
	* Add JS script inline and, in case of debugging mode, add information's
	* about the caller
	*/
	public static function addJavascriptInline(string $js) : string
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$caller = '';
		if ($aeSettings->getDebugMode()) {
			$trace = debug_backtrace();
			$caller = ($trace[1]['class'] ?? '').'::'.($trace[1]['function'] ?? '');
			$caller .= ' line '.$trace[0]['line'];
		}

		if ($aeSettings->getDebugMode()) {
			$js = "\n<!-- Lines below are added by ".$caller."-->\n".
			trim($js, "\n")."\n".
			"<!-- End for ".$caller."-->\n";
		}

		return $js;
	}

	/**
	* Generic function for adding a js in the HTML response
	*
	* @param  type $localfile
	* @param  type $weblocation
	* @return string
	*/
	public static function addJavascript(string $localfile, string $weblocation = '', bool $defer = false) : string
	{
		$return = '';

		// Perhaps the script (aesecure_quickscan.php) is a symbolic link so __DIR__ is the folder where the
		// real file can be found and SCRIPT_FILENAME his link, the line below should therefore not be used anymore

		$aeFiles = \MarkNotes\Files::getInstance();
		if ($aeFiles->exists(dirname($_SERVER['SCRIPT_FILENAME']).'/'.$localfile)) {
			$return = '<script '.($defer == true?'defer="defer" ':'').'type="text/javascript" src="'.$localfile.'">'.
			'</script>';
		} elseif ($aeFiles->exists($localfile)) {
			// It's a full, local, filename
			$localfile = str_replace(dirname(dirname($_SERVER['SCRIPT_FILENAME'])), '', str_replace(DS, '/', $localfile));
			$return = '<script '.($defer == true?'defer="defer" ':'').'type="text/javascript" src="'.$localfile.'"></script>';
		} else {
			if ($weblocation != '') {
				$return = '<script '.($defer == true?'defer="defer" ':'').'type="text/javascript" src="'.$weblocation.'">'.
				'</script>';
			}
		}

		return $return;
	}

	/**
	* Generic function for adding a css in the HTML response
	*
	* @param  type $localfile
	* @param  type $weblocation
	* @return string
	*/
	public static function addStylesheet(string $localfile, string $weblocation = '') : string
	{
		$return = '';

		// Perhaps the script (aesecure_quickscan.php) is a symbolic link so __DIR__ is the folder where the
		// real file can be found and SCRIPT_FILENAME his link, the line below should therefore not be used anymore

		$aeFiles = \MarkNotes\Files::getInstance();
		if ($aeFiles->exists(dirname($_SERVER['SCRIPT_FILENAME']).'/'.$localfile)) {
			// It's a relative filename
			$return = '<link media="screen" rel="stylesheet" type="text/css" href="'.$localfile.'" />';
		} elseif ($aeFiles->exists($localfile)) {
			// It's a full, local, filename
			$localfile = str_replace(dirname(dirname($_SERVER['SCRIPT_FILENAME'])), '', str_replace(DS, '/', $localfile));
			$return = '<link media="screen" rel="stylesheet" type="text/css" href="'.$localfile.'" />';
		} else {
			if ($weblocation != '') {
				$return = '<link media="screen" rel="stylesheet" type="text/css" href="'.$weblocation.'" />';
			}
		}

		return $return;
	}

	/**
	* Wrapper for array_unique but for insensitive comparaison  (Images or images should be considered as one value)
	*
	* @link	http://stackoverflow.com/a/2276400
	* @param  array $array
	* @return array
	*/
	public static function array_iunique(array $array) : array
	{
		return array_intersect_key($array, array_unique(array_map("StrToLower", $array)));
	}

	/**
	* Return true when the call to the php script has been done through an ajax request
	*
	* @return type
	*/
	public static function isAjaxRequest() : bool
	{
		$bAjax = boolval(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

		return $bAjax;
	}

	/**
	* For instance :
	*	 if (startsWith("Debug - This is a test", "Debug")) {
	*		 // Debug mode ...
	*	 }
	*
	*/
	public static function startsWith(string $sLine, string $sPattern) : bool
	{
		$length = strlen($sPattern);
		return (substr($sLine, 0, $length) === $sPattern);
	}

	public static function endsWith(string $sLine, string $sPattern) : bool
	{
		$length = strlen($sPattern);

		return $length === 0 ||
		(substr($sLine, -$length) === $sPattern);
	}
}
