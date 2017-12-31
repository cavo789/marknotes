<?php
/**
 * Working with folders.
 *
 * Note : this class will handle two types of paths, real or
 * symbolic.
 *
 * Symbolic link concern the folders that are not "really" in the
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
 *
 * IMPORTANT NOTES CONCERNING FOLDERS WITH FLYSYSTEM :
 * https://flysystem.thephpleague.com/core-concepts/
 *
 * "Flysystem has a files first approach. Storage systems like
 * AWS S3 are linear file systems, this means the path to a file
 * is used as an identifier, rather than a representation of all
 * the directories it’s nested in."
 * "This means directories are second class citizens. Because of
 * this, directories will be automatically created on file systems
 * that require them when writing files. Not only does this make
 * handling writes a lot easier, it also ensures a consistent
 * behaviors across all file system types."
 */

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;

class Folders
{
	protected static $hInstance = null;
	protected static $flyWebRoot = null; // web root
	protected static $flyAppRoot = null; // application root
	protected static $sWebRoot = '';
	protected static $sAppRoot = '';

	/**
	 * Create an instance of MarkNotes\Folders and Initialize
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
			self::$hInstance = new Folders();
		}
		return self::$hInstance;
	}

	/**
	* Check if a folder exists and return FALSE if not.
	* With FlySystem. Note : with FlySystem, if a folder doesn't
	* exists, it will be created automatically when a file is
	* created so, for that usage, it isn't really important to check
	* if the folder already exists
	*
	* @param  type $filename
	* @return boolean
	*/
	public static function exists(string $foldername) : bool
	{
		if ($foldername == '') {
			return false;
		}

		$foldername = str_replace('/', DS, $foldername);

		if (strpos($foldername, static::$sAppRoot)!==FALSE) {
			// The folder is stored in the application folder
			$foldername = str_replace(static::$sAppRoot, '', $foldername);
			$wReturn = static::$flyAppRoot->has($foldername);
		}  else {
			// The folder is stored in the webroot folder
			$foldername = str_replace(static::$sWebRoot, '', $foldername);
			$wReturn = static::$flyWebRoot->has($foldername);
		}

		return $wReturn;
	}

	/**
	 * Create a folder
	 * With FlySystem. Note : with FlySystem, if a folder doesn't
	 * exists, it will be created automatically when a file is
	 * created so, for that usage, it isn't really important to check
	 * if the folder already exists
	 */
	public static function create(string $foldername) : bool
	{
		$wReturn = 0;
		$foldername = str_replace('/', DS, $foldername);
		$arr = array('visibility' => AdapterInterface::VISIBILITY_PUBLIC);

		try {
			if (strpos($foldername, static::$sAppRoot)!==FALSE) {
				// The folder should be created in the application folder
				$foldername = str_replace(static::$sAppRoot, '', $foldername);
				static::$flyAppRoot->createDir($foldername, $arr);
				$wReturn = static::$flyAppRoot->has($foldername);
			} else {
				// The folder should be created in the webroot folder
				$foldername = str_replace(static::$sWebRoot, '', $foldername);
				static::$flyWebRoot->createDir($foldername, $arr);
				$wReturn = static::$flyWebRoot->has($foldername);
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
	 * Rename an existing folder
	 * With FlySystem.
	 */
	public static function rename(string $oldname, string $newname) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();

		$oldname = str_replace('/', DS, $oldname);
		$newname = str_replace('/', DS, $newname);

		$bReturn = false;

		if (self::exists($oldname) && ($oldname !== $newname)) {
			try {
				if (strpos($oldname, static::$sAppRoot)!==FALSE) {
					// The folder should be renamed in the application
					// folder
					$oldname = str_replace(static::$sAppRoot, '', $oldname);
					$newname = str_replace(static::$sAppRoot, '', $newname);
					static::$flyAppRoot->rename($oldname, $newname);
					$bReturn = static::$flyAppRoot->has($newname);
				} else {
					// The folder should be renamed in the webroot folder
					$oldname = str_replace(static::$sWebRoot, '', $oldname);
					$newname = str_replace(static::$sWebRoot, '', $newname);

					static::$flyWebRoot->rename($oldname, $newname);
					$bReturn = static::$flyWebRoot->has($newname);
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

		return $bReturn;
	}

	/**
	 * Delete a folder and his subfolders if any
	 * With FlySystem : the deletion is recursive, nothing to
	 * code for this
	 */
	public static function delete(string $name) : bool
	{
		if ($name == '') {
			return false;
		}

		$name = str_replace('/', DS, $name);

		if (strpos($name, static::$sWebRoot)!==FALSE) {
			// The folder is stored in the webroot folder
			$name = str_replace(static::$sWebRoot, '', $name);
			$wReturn = static::$flyWebRoot->deleteDir($name);
		}  else {
			// The folder is stored in the application folder
			$name = str_replace(static::$sAppRoot, '', $name);
			$wReturn = static::$flyAppRoot->deleteDir($name);
		}

		return $wReturn;
	}

	/**
	 * Get the list of files/folders under $path, recursively or not
	 *
	 * $path should be a folder, can't be a pattern like 'file.*'
	 */
	public static function getContent(string $path, bool $recursive = false) : array
	{
		$path = str_replace('/', DS, $path);

		if (strpos($path, static::$sWebRoot)!==FALSE) {
			// The folder is stored in the webroot folder
			$path = str_replace(static::$sWebRoot, '', $path);
			$items = static::$flyWebRoot->listContents($path, false);
		}  else {
			// The folder is stored in the application folder
			$path = str_replace(static::$sAppRoot, '', $path);
			$items = static::$flyAppRoot->listContents($path, false);
		}

		return $items;
	}

}
