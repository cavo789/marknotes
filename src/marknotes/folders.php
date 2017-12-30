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
	* Check if a file exists and return FALSE if not.
	* Disable temporarily errors to avoid warnings f.i. when the file
	* isn't reachable due to open_basedir restrictions
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
		// Should be relative to the webroot
		$path = str_replace('/', DS, $path);
		$path = str_replace(static::$sWebRoot, '', $path);

		// Get the full list of files/folders
		$items = static::$flyWebRoot->listContents($path, false);

		return $items;
	}

}
