<?php
/**
 * htpasswd - Add a user in the .htpasswd file
 *
 * Answer to URL like index.php?task=task.htpasswd.add&param=xxxx
 */
namespace MarkNotes\Plugins\Task\htpasswd\helper;

defined('_MARKNOTES') or die('No direct access allowed');

class Manage
{
	protected static $htaccessFile = '';
	protected static $htpasswdFile = '';

	private static $lines =
		'AuthUserFile $1'.PHP_EOL.
		'AuthName "Protected area"'.PHP_EOL.
		'AuthType Basic'.PHP_EOL.
		'Require valid-user'.PHP_EOL.PHP_EOL;

	public function __construct(string $htaccessFile = '', string $htpasswdFile = '')
	{
		self::$htaccessFile = $htaccessFile;
		self::$htpasswdFile = $htpasswdFile;

		return true;
	}

	/**
	 * Remove lines from .htaccess to remove the call to the
	 * .htpasswd file
	 */
	private static function removeProtection(string $content) : string
	{
		return $content;
	}

	/**
	 * Add lines to the .htaccess to add the call to the
	 * .htpasswd file
	 */
	private static function addProtection(string $content) : string
	{
		if (strpos('Require valid-user', $content) === FALSE) {
			// Add lines in the .htaccess file to implement
			// the .htpasswd protection.

			$file = str_replace(DS, '/', self::$htpasswdFile);

			$sLine = self::$lines;
			$sLine = str_replace('$1', $file, $sLine);

			$content = $sLine.$content;
		}

		return $content;
	}

	public static function add(string $username, string $password) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$dir = rtrim(dirname(__DIR__), DS).DS;
		$lib = $dir.'libs/ozanhazer/Htpasswd.php';

		require_once($lib);

		$aeFiles = \MarkNotes\Files::getInstance();

		if (!($aeFiles->exists(self::$htpasswdFile))) {
			$aeFiles->create(self::$htpasswdFile, '');
		}

		$htpasswd = new \Htpasswd(self::$htpasswdFile);
		$htpasswd->addUser($username, $password);

		$htaccess = $aeFiles->getContent(self::$htaccessFile);

		// Add lines in the .htaccess only if the .htpasswd
		// file isn't empty or remove lines in the .htpasswd file
		// is empty
		if (filesize(self::$htpasswdFile)>0) {
			$htaccess = self::addProtection($htaccess);
		} else {
			// .htpasswd file empty, should not be the case
			// but just to be sure...
			$aeFiles->delete(self::$htpasswdFile);
			$htaccess = self::removeProtection($htaccess);
		}
/*<!-- build:debug -->*/
echo ("<pre style='background-color:yellow;'>".__FILE__." - ".__LINE__.PHP_EOL.str_replace('<','&lt;',trim($htaccess))."</pre>");
/*<!-- endbuild -->*/
die();
		// Rewrite the .htaccess
		$aeFiles->rewrite(self::$htaccessFile, $htaccess);

		return true;
	}
}
