<?php
/**
 * Working with files.
 *
 * Note : this class will handle two types of paths, real or
 * symbolic.
 *
 * Symbolic link concern the files that are not "really" in the
 * web folder (f.i. c:\sites\notes) but elsewhere (f.i.
 * c:\repo\marknotes). In the web folder, we can have a folder like
 * the "marknotes" folder (which contains the source code of marknotes)
 * and that folder is a symbolic link to c:\repo\marknotes.
 *
 * By checking if the folder "marknotes" exists in "c:\sites\notes",
 * the result will be False. We need to check "c:\repo\marknotes".
 *
 * Therefore, before checking if a file/folder exists, we need to
 * check if the path is for the "web folder" or the
 * "application folder".
 *
 * Using Flysystem : @https://github.com/thephpleague/flysystem
 */

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;

class Files
{
	protected static $hInstance = null;
	protected static $flyWebRoot = null; // web root
	protected static $flyAppRoot = null; // application root
	protected static $sWebRoot = '';
	protected static $sAppRoot = '';

	/**
	 * Create an instance of MarkNotes\Files and Initialize
	 * the $flyWebRoot object and, if needed, $flyAppRoot
	 */
	public function __construct()
	{
		// Get the root folder of marknotes (f.i. C:\sites\marknotes\
		// or /home/html/sites/marknotes/)
		self::$sWebRoot=trim(dirname($_SERVER['SCRIPT_FILENAME']), DS);
		self::$sWebRoot=str_replace('/', DS, self::$sWebRoot).DS;

		// Application root folder.
		self::$sAppRoot = rtrim(dirname(dirname(__DIR__)), DS).DS;
		self::$sAppRoot = str_replace('/', DS, self::$sAppRoot);

		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			self::$sWebRoot = DS.ltrim(self::$sWebRoot, DS);
			self::$sAppRoot = DS.ltrim(self::$sAppRoot, DS);
		}

		$adapter = new Local(static::$sWebRoot);

		// With Flysystem (https://flysystem.thephpleague.com),
		// we can use multiple adapter for, for instance, the local
		// system (FileSystem), for Azure, Dropbox, FTP, WebDAV, ...
		// See https://flysystem.thephpleague.com/adapter/local/
		// So, here below, we are choising for FileSystem i.e. local
		// folder
		static::$flyWebRoot = new Filesystem($adapter);

		// When using symbolic link, the application root (i.e.
		// the folder when marknotes source files are stored) is
		// perhaps different than the web root (where the notes
		// are stored). We then need to have two objects
		if (self::$sWebRoot!==self::$sAppRoot) {
			$adapter = new Local(static::$sAppRoot);
			static::$flyAppRoot = new Filesystem($adapter);
		}

		return true;
	}

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Files();
		}
		return self::$hInstance;
	}

	/**
	 * Check if a file exists and return FALSE if not.
	 * With FlySystem
	 *
	 * @param  type $filename
	 * @return boolean
	 */
	public static function exists(string $filename) : bool
	{
		if ($filename == '') {
			return false;
		}

		$filename = str_replace('/', DS, $filename);

		if (strpos($filename, static::$sAppRoot)!==FALSE) {
			// The file is stored in the application folder
			$filename = str_replace(static::$sAppRoot, '', $filename);
			$wReturn = static::$flyAppRoot->has($filename);
		}  else {
			// The file is stored in the webroot folder
			$filename = str_replace(static::$sWebRoot, '', $filename);
			$wReturn = static::$flyWebRoot->has($filename);
		}

		return $wReturn;
	}

	/**
	 * Create a file
	 * With FlySystem
	 */
	public static function create(string $filename, string $content) : bool
	{
		$wReturn = 0;
		$filename = str_replace('/', DS, $filename);
		$arr = array('visibility' => AdapterInterface::VISIBILITY_PUBLIC);

		try {
			if (strpos($filename, static::$sAppRoot)!==FALSE) {
				// The file should be stored in the application folder
				$filename = str_replace(static::$sAppRoot, '', $filename);
				static::$flyAppRoot->write($filename, $content, $arr);
				$wReturn = static::$flyAppRoot->has($filename);
			} else {
				// The file should be stored in the webroot folder
				$filename = str_replace(static::$sWebRoot, '', $filename);
				static::$flyWebRoot->write($filename, $content, $arr);
				$wReturn = static::$flyWebRoot->has($filename);
			}
		} catch (Exception $ex) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				echo $ex->getMessage();
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->here("", 99);
			}
			/*<!-- endbuild -->*/
		}

		return $wReturn;
	}

	/**
	 * Rename an existing file
	 * With FlySystem
	 */
	public static function rename(string $oldname, string $newname) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$wReturn = false;

		if ((self::exists($oldname)) && ($oldname !== $newname)) {
			try {
				if (strpos($filename, static::$sAppRoot)!==FALSE) {
					// The file should be stored in the application folder
					$oldname = str_replace(static::$sAppRoot, '', $oldname);
					$newname = str_replace(static::$sAppRoot, '', $newname);
					static::$flyAppRoot->rename($oldname, $newname);
					$wReturn = static::$flyAppRoot->has($newname);
				} else {
					// The file should be stored in the webroot folder
					$oldname = str_replace(static::$sWebRoot, '', $oldname);
					$newname = str_replace(static::$sWebRoot, '', $newname);
					static::$flyWebRoot->rename($oldname, $newname);
					$wReturn = static::$flyWebRoot->has($newname);
				}
			} catch (Exception $ex) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					echo $ex->getMessage();
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->here("", 99);
				}
				/*<!-- endbuild -->*/
			}
		}

		return $wReturn;
	}

	/**
	 * Remove a file
	 * With FlySystem
	 */
	public static function delete(string $filename) : bool
	{
		if ($filename == '') {
			return false;
		}

		$filename = str_replace('/', DS, $filename);

		if (strpos($filename, static::$sWebRoot)!==FALSE) {
			// The file is stored in the webroot folder
			$filename = str_replace(static::$sWebRoot, '', $filename);
			$wReturn = static::$flyWebRoot->delete($filename);
		}  else {
			// The file is stored in the application folder
			$filename = str_replace(static::$sAppRoot, '', $filename);
			$wReturn = static::$flyAppRoot->delete($filename);
		}

		return $wReturn;
	}

	/**
	 * Get the list of files/folders under $path, recursively or not
	 *
	 * $path should be a folder, can't be a pattern like 'file.*'
	 */
	public static function getContent(string $filename, bool $recursive = false) : string
	{
		$filename = str_replace('/', DS, $filename);

		if (strpos($filename, static::$sWebRoot)!==FALSE) {
			// The file is stored in the webroot folder
			$filename = str_replace(static::$sWebRoot, '', $filename);
			$content = static::$flyWebRoot->read($filename);
		}  else {
			// The file is stored in the application folder
			$filename = str_replace(static::$sAppRoot, '', $filename);
			$content = static::$flyAppRoot->read($filename);
		}

		return $content;
	}

	/**
	 * Rewrite an existing file : update his content by a new one
	 * With FlySystem
	 *
	 * @param  string $filename	Absolute filename
	 * @param  string $content	The new content
	 * @return bool				return False in case of error
	 */
	public static function rewrite(string $filename, string $content) : bool
	{
		$bReturn = false;

		$filename = str_replace('/', DS, $filename);
		try {
			if (strpos($filename, static::$sAppRoot)!==FALSE) {
				// The file should be stored in the application folder
				$filename = str_replace(static::$sAppRoot, '', $filename);
				static::$flyAppRoot->update($filename, $content);
				$wReturn = static::$flyAppRoot->has($filename);
			} else {
				// The file should be stored in the webroot folder
				$filename = str_replace(static::$sWebRoot, '', $filename);
				static::$flyWebRoot->update($filename, $content);
				$wReturn = static::$flyWebRoot->has($filename);
			}
		} catch (Exception $ex) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				echo $ex->getMessage();
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->here("", 99);
			}
			/*<!-- endbuild -->*/
		}
		return $bReturn;
	}

	/**
	 * Get the timestamp (last modification date) of a file
	 * With FlySystem
	 */
	public static function timestamp(string $filename) : string
	{
		if ($filename == '') {
			return false;
		}

		$filename = str_replace('/', DS, $filename);

		if (strpos($filename, static::$sWebRoot)!==FALSE) {
			// The file is stored in the webroot folder
			$filename = str_replace(static::$sWebRoot, '', $filename);
			$wReturn = static::$flyWebRoot->getTimestamp($filename);
		}  else {
			// The file is stored in the application folder
			$filename = str_replace(static::$sAppRoot, '', $filename);
			$wReturn = static::$flyAppRoot->getTimestamp($filename);
		}
		return $wReturn;
	}

	/**
	 * Write a content into a UTF8-BOM file
	 */
	/*public static function fwriteUTF8BOM(string $sFileName,
		string $sContent)
	{
		$f = fopen($sFileName, "wb");
		fputs($f, "\xEF\xBB\xBF".$sContent);
		fclose($f);
	}*/
	/**
	 * Under Windows, create a text file with the support of
	 * UTF8 in his content.
	 */
	/*public static function fwriteANSI(string $sFileName, string $sContent)
	{
		file_put_contents($sFileName, utf8_encode($sContent));
		return true;
	}*/

	/**
	* Recursive glob : retrieve all files that are under
	* $path (if empty, $path is the root folder of the website)
	*
	* For instance :
	*		aeSecureFct::rglob('.htaccess', $rootFolder);
	* 		to find every .htaccess files on the server
	*
	* If folders should be skipped :
	*
	*		aeSecureFct::rglob('.htaccess', $rootFolder,
	*			0, array('aesecure','administrator'))
	*
	* @param  type $pattern
	* @param  type $path
	* @param  type $flags
	* @param  type $arrSkipFolder Folders to skip...
	* @return type
	*/
	public static function rglob(string $pattern = '*', string $path = '', int $flags = 0, $arrSkipFolder = null) : array
	{
		static $adjustCase = false;

		// glob() is case sensitive so, search for PHP isn't searching for php.
		// Here, the pattern will be changed to be case insensitive.
		// "*.php" will be changed to "*.[pP][hH][pP]"

		if (($pattern != '') && ($adjustCase == false)) {
			$length = strlen($pattern);
			$tmp = $pattern;
			$pattern = '';
			for ($i = 0; $i < $length; $i++) {
				$pattern .= (ctype_alpha($tmp[$i]) ? '['.strtolower($tmp[$i]).strtoupper($tmp[$i]).']' : $tmp[$i]);
			}
			// Do this only once
			$adjustCase = true;
		}

		// If the "$path" is one of the folder to skip, ... skip it.

		if (($arrSkipFolder != null) && (count($arrSkipFolder) > 0)) {
			foreach ($arrSkipFolder as $folder) {
				if (self::startsWith($folder, $path)) {
					return null;
				}
			} // foreach
		} // if (($arrSkipFolder!=null) && (count($arrSkipFolder)>0))

		$paths = glob($path.'*', GLOB_MARK | GLOB_ONLYDIR);

		// Sort, case insensitive
		usort($paths, 'strnatcasecmp');

		$files = glob(rtrim($path, DS).DS.$pattern, $flags);
		// Sort, case insensitive
		usort($files, 'strnatcasecmp');

		foreach ($paths as $path) {
			// Avoid recursive loop when the folder is a symbolic link
			if (rtrim(str_replace('/', DS, $path), DS) == realpath($path)) {
				$arr = self::rglob($pattern, $path, $flags, $arrSkipFolder);
				if (($arr != null) && (count($arr) > 0)) {
					$files = array_merge($files, $arr);
				}
			} else {
				// $path is a symbolic link.  Doing a glob on a symbolic link will create a recursive
				// call and will crash the script
			}
		} // foreach

		// Don't use the / notation but well the correct directory separator
		foreach ($files as $key => $value) {
			$files[$key] = $value;
		}

		return $files;
	}

	/**
	* Replace file's extension
	 *
	* @param  string $filename	  The filename ("test.md")
	* @param  string $new_extension The new extension ("html")
	* @return string				The new filename (test.html)
	*/
	public static function replaceExtension(string $filename, string $new_extension) : string
	{
		$info = pathinfo($filename);

		$sResult = self::removeExtension($filename).'.'.$new_extension;

		return $sResult;
	}

	/**
	* Remove file's extension
	 *
	* @param  string $filename The filename ("test.md")
	* @return string				The new filename (test)
	*/
	public static function removeExtension(string $filename) : string
	{
		// Correctly handle double extension like docs\development\marknotes.reveal.pdf
		$arr = explode('.', $filename);

		$extension = '';
		if (count($arr) > 0) {
			// Remove the last extension and save it into $extension
			$extension = array_pop($arr);
		}

		return str_replace('.'.$extension, '', $filename);
	}

	/**
	* Get file's extension
	 *
	* @param  string $filename The filename ("test.md")
	* @return string				The new filename (test)
	*/
	public static function getExtension(string $filename) : string
	{
		$filename = basename($filename);

		// Correctly handle double extension like docs\development\marknotes.reveal.pdf
		$arr = explode('.', $filename);

		$extension = '';
		if (count($arr) > 0) {
			unset($arr[0]);
			$sResult = implode($arr, '.');
		}

		return $sResult;
	}

	/**
	* Be sure that the filename isn't something like f.i. ../../../../dangerous.file
	* Remove dangerouse characters and remove ../
	*
	* @param  string $filename
	* @return string
	*
	* @link http://stackoverflow.com/a/2021729/1065340
	*/
	public static function sanitize(string $filename) : string
	{
		// Remove anything which isn't a word, whitespace, number
		// or any of the following caracters -_~,;[]().
		// If you don't need to handle multi-byte characters
		// you can use preg_replace rather than mb_ereg_replace
		// Thanks @Łukasz Rysiak!

		// Remove any trailing dots, as those aren't ever valid file names.
		$filename = rtrim($filename, '.');

		// Replace characters not in the list below by a dash (-)
		// For instance : single quote, double-quote, parenthesis, ...
		// The list mentionned below is thus the allowed characters
		$regex = array('#[^: A-Za-z0-9&_àèìòùÀÈÌÒÙáéíóúýÁÉÍÓÚÝâêîôûÂÊÎÔÛãñõÃÑÕäëïöüÿÄËÏÖÜŸçÇ\.\\\/\_\- ]#');
		$filename = trim(preg_replace($regex, '-', $filename));

		// Don't allow a double .. in the name and don't allow to start with a dot
		$regex = array('#(\.){2,}#', '#^\.#');
		$filename = trim(preg_replace($regex, '', $filename));

		// If $filename was f.i. '../../../../../'.$filename
		// the preg_replace has change it to '/////'.$filename so remove leading /
		// Remove directory separator for Unix and Windows

		$filename = ltrim($filename, '\\\/');

		return $filename;
	}
}
