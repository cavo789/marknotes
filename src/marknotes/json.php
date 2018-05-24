<?php
/* REQUIRES PHP 7.x AT LEAST */

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

define('JSON_FILE_NOT_FOUND', 'The file [%s] doesn\'t exists (anymore)');

class JSON
{
	protected static $hInstance = null;

	private static $hDebug = false;
	private static $root = '';

	public function __construct(string $root = '')
	{
		if ($root!=='') {
			self::$root = rtrim($root, DS).DS;
		} else {
			self::$root = rtrim(dirname($_SERVER['SCRIPT_FILENAME']), DS).DS;
			self::$root = str_replace('/', DS, self::$root);
		}

		self::$hDebug = false;
		return true;
	}

	public static function getInstance(string $root = '')
	{
		if (self::$hInstance === null) {
			self::$hInstance = new JSON($root);
		}
		return self::$hInstance;
	}

	/**
	 * !!! Under PHP P7.0 the "object" type seems to be not supported.
	 * The $e parameter is thus untype
	 *
	 * @param  object $e	 [description]
	 * @param  string $fname [description]
	 * @param  string $json  [description]
	 * @return bool		  [description]
	 */
	private static function showLintError($e, string $fname, string $json) : bool
	{
		if (self::$hDebug == true) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->here("", 4);
		}

		// Get the error message returned by JSON Lint
		$msg = $e->getMessage();

		header('Content-Type: text/html; charset=utf-8');
		echo '<h2>The syntax of '.$fname.' is invalid</h2>';
		echo '<h3>Error message</h3>';
		echo $msg;

		echo '<h3>JSON content</h3>';

		$tmp = '';
		$arr = preg_split("/((\r?\n)|(\r\n?))/", $json);
		$wNbr = 0;
		foreach ($arr as $line) {
			$wNbr++;
			$tmp .= sprintf('%05d', $wNbr).'  '.$line.PHP_EOL;
		}

		echo("<pre>".PHP_EOL.print_r($tmp, true)."</pre>");

		die();
	}

	private static function showError(string $param, bool $die = true) : bool
	{
		if (trim($param) !== '') {
			$param .= ' - ';
		}

		$msg = '';

		switch (json_last_error()) {
			case JSON_ERROR_DEPTH:
				$msg = $param.'Maximum stack depth exceeded [error code JSON_ERROR_DEPTH]';
				break;

			case JSON_ERROR_STATE_MISMATCH:
				$msg = $param.'Underflow or the modes mismatch [error code JSON_ERROR_STATE_MISMATCH]';
				break;

			case JSON_ERROR_CTRL_CHAR:
				$msg = $param.'Unexpected control character found [error code JSON_ERROR_CTRL_CHAR]';
				break;

			case JSON_ERROR_SYNTAX:
				$msg = $param.'Syntax error, malformed JSON [error code JSON_ERROR_SYNTAX] '.
				'(be sure file is UTF8-NoBOM and is correct (use jsonlint.com to check validity))';
				break;

			case JSON_ERROR_UTF8:
				$msg = $param.'Malformed UTF-8 characters, possibly incorrectly encoded [error code JSON_ERROR_UTF8]';
				break;

			default:
				$msg = $param.'Unknown error';
				break;
		} // switch (json_last_error())

		if (self::$hDebug == true) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->here("", 4);
		}

		$msg = '<div class="error bg-danger">ERROR - '.$msg.'</div>';

		if ($die === true) {
			die($msg);
		} else {
			echo $msg;
			return true;
		}
	}

	/**
	* Enable or not the debug mode i.e. display additionnal infos in case of errors
	*
	* @param bool $bState TRUE/FALSE
	*/
	public function debug(bool $bState)
	{
		self::$hDebug = $bState;
	}

	private static function loadLib()
	{
		// Load JsonLint library
		$lib = self::$root.'libs/jsonlint/Seld/JsonLint/';

		require_once($lib.'ParsingException.php');
		require_once($lib.'DuplicateKeyException.php');
		require_once($lib.'Undefined.php');
		require_once($lib.'Lexer.php');
		require_once($lib.'JsonParser.php');

		return true;
	}

	/**
	* json_decode with error handling.  Show error message in case of problem
	*
	* @param  string $fname Absolute filename
	* @param  bool	$assoc [optional] When TRUE, returned objects will be converted into associative arrays.
	* @return type
	*/
	public static function json_decode(string $fname, bool $assoc = false)
	{
		$aeFiles = \MarkNotes\Files::getInstance();

		//if (!$aeFiles->exists($fname)) {
		//	$fname = utf8_decode($fname);
		//}

		if (!$aeFiles->exists($fname)) {
			self::showError(str_replace('%s', '<strong>'.$fname.'</strong>', JSON_FILE_NOT_FOUND), true);
		}

		// Load JsonLint library
		self::loadLib();

		// Trim() so we're sure there is no whitespace
		// before the JSON content
		$value = trim($aeFiles->getContent($fname));

		// Load the JSON parser
		// Because files settings.json can be manually changed by
		// the user, the risk of syntax error is high so
		$parser = new \Seld\JsonLint\JsonParser();

		try {
			if (($e = $parser->lint($value))!==null) {
				self::showLintError($e, $fname, $value);
			} else {
				$arr = json_decode($value, $assoc);
			}
		} catch (Exception $e) {
			header('Content-Type: text/html; charset=utf-8');

			self::showError($fname, false);

			if (self::$hDebug) {
				echo '<pre>'.$aeFiles->getContent($fname).'</pre>';
				echo "<pre style='background-color:yellow;'>".__FILE__." - ".__LINE__." ".print_r($e->getMessage(), true)."</pre>";
			}
		}
		return $arr;
	}

	public static function json_encode($value, int $option = JSON_PRETTY_PRINT) : string
	{
		$return = '';

		try {
			$return = json_encode($value, $option);

			if (json_last_error() === JSON_ERROR_UTF8) {
				// In case of UTF8 error, just try to encode the string and json_encode again
				if (!is_array($value)) {
					$return = json_encode(iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($value)), $option);
				}
			}

			if (json_last_error() !== JSON_ERROR_NONE) {
				header('Content-Type: text/html; charset=utf-8');

				self::showError('', false);

				if (self::$hDebug) {
					echo '<pre style="background-color:yellow;">'.print_r($value, true).'</pre>';
				}

				die();
			} // if (json_last_error()!==JSON_ERROR_NONE)
		} catch (Exception $ex) {
			self::showError($ex->getMessage(), true);
		}
		return $return;
	}
}
