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
 * the "marknotes" folder (which contains the source code of
 * marknotes) and that folder is a symbolic link to
 * c:\repo\marknotes.
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
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

class Folders
{
	protected static $hInstance = null;
	protected static $flyWebRoot = null; // web root
	protected static $flyAppRoot = null; // application root
	protected static $sWebRoot = '';
	protected static $sAppRoot = '';

	// Root folder to the /docs folder i.e. where notes are stored
	protected static $sDocsRoot = '';
	// "FlySystem" for the documentation (can be on Dropbox f.i.)
	protected static $flyDocsRoot = null;

	/**
	 * Create an instance of MarkNotes\Folders and Initialize
	 * the $flyWebRoot object and, if needed, $flyAppRoot
	 */
	public function __construct(string $webroot = '')
	{
		// Get the root folder of marknotes (f.i. C:\sites\marknotes\
		// or /home/html/sites/marknotes/)
		if ($webroot=='') {
			self::$sWebRoot=rtrim(dirname($_SERVER['SCRIPT_FILENAME']), DS);
			self::$sWebRoot=str_replace('/', DS, self::$sWebRoot).DS;
		} else {
			self::$sWebRoot = rtrim($webroot, DS).DS;
		}

		// Application root folder.
		self::$sAppRoot = rtrim(dirname(dirname(__DIR__)), DS).DS;
		self::$sAppRoot = str_replace('/', DS, self::$sAppRoot);

		// Default : empty; will be initialized by setDocFolder()
		self::$sDocsRoot = '';

		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
			self::$sWebRoot = DS.ltrim(self::$sWebRoot, DS);
			self::$sAppRoot = DS.ltrim(self::$sAppRoot, DS);
		}

		// Local::SKIP_LINKS = don't throw fatal errors when
		// a symlink file/folder is found, just ignore it
		// https://flysystem.thephpleague.com/adapter/local/
		$adapter = new Local(static::$sWebRoot, LOCK_EX, Local::SKIP_LINKS);

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
			// Local::SKIP_LINKS = don't throw fatal errors when
			// a symlink file/folder is found, just ignore it
			// https://flysystem.thephpleague.com/adapter/local/
			$adapter = new Local(static::$sAppRoot, LOCK_EX, Local::SKIP_LINKS);
			static::$flyAppRoot = new Filesystem($adapter);
		}

		return true;
	}

	public static function getInstance(string $webroot='')
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Folders($webroot);
		}
		return self::$hInstance;
	}

	/**
	 * Initialize the "cloud filesystem" that will then allow to
	 * work with Dropbox, Amazon S3, ... or just the FileSystem
	 */
	public static function setDocFolder(array $arr, string $docFolder) : bool
	{
		$enabled = boolval($arr['enabled']);
		$platform = strtolower($arr['platform']);
		self::$sDocsRoot = $docFolder;

		if ($enabled && ($platform!=='')) {
			// Be sure that we've a token
			if (!isset($arr['token'])) {
				throw new \Exception('FATAL ERROR - No token '.
					'has been provided; you need to specify one '.
					'in the settings.json file, node cloud->token '.
					'as soon as a value has been specified for '.
					'cloud->platform. If you don\'t use a cloud '.
					'system, leave cloud->platform empty.');
			}

			// Get it
			$token = trim($arr['token']);

			if ($platform=='dropbox') {
				$client = new Client($token);
				$adapter = new DropboxAdapter($client);

			}
		} else { // if ($platform!=='')
			// Local::SKIP_LINKS = don't throw fatal errors when
			// a symlink file/folder is found, just ignore it
			// https://flysystem.thephpleague.com/adapter/local/
			$adapter = new Local(static::$sDocsRoot, LOCK_EX, Local::SKIP_LINKS);
		}

		static::$flyDocsRoot = new Filesystem($adapter);
		return true;
	}

	/**
	 * Based on the $path, determine which "filesystem" should
	 * be used.
	 *
	 * In January 2018, three are three filesystems :
	 *
	 *	- One for the documentation folder i.e. where the notes are
	 *		stored (path is something like c:\site\marknotes\docs)
	 *	- One for the webroot folder i.e. the root folder
	 *		(path is something like c:\site\marknotes)
	 *	- One for the application folder i.e. where the application
	 *		is stored (path can be something like
	 *		c:\repository\marknotes). This is only usefull when
	 *		some folders are not real in the webfolder but are symlinks
	 *
	 * The function below will receive an absolute path
	 * (f.i. c:\site\marknotes\docs\subfolder\note.md) and will
	 * decide which "filesystem" should be used.
	 * The function will also modify the $path absolute variable and
	 * make it relative (f.i. subfolder\note.md)
	 */
	private static function getFileSystem(string &$foldername, &$obj)
	{
		$foldername = str_replace('/', DS, $foldername);

		if ((self::$sDocsRoot!=='') && (strpos($foldername, self::$sDocsRoot)!==FALSE)) {
			// The folder is stored in the /docs folder
			// ==> can be on a cloud
			$foldername = str_replace(static::$sDocsRoot, '', $foldername);
			$obj = static::$flyDocsRoot;
		} else if (strpos($foldername, static::$sWebRoot)!==FALSE) {
			// The folder is stored in the webroot folder
			$foldername = str_replace(static::$sWebRoot, '', $foldername);
			$obj = static::$flyWebRoot;
		} else {
			// The folder is stored in the application folder
			$foldername = str_replace(static::$sAppRoot, '', $foldername);
			$obj = static::$flyAppRoot;
		}

		return true;
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

		self::getFileSystem($foldername, $obj);
		// when $foldername is empty, this means that the folder
		// is f.i. c:\sites\marknotes\docs i.e. a root folder
		// (of one of the "filesystem" so the folder exists)
		return ($foldername=='' ? true : $obj->has($foldername));
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
		if ($foldername == '') {
			return false;
		}

		$arr = array('visibility' => AdapterInterface::VISIBILITY_PUBLIC);

		self::getFileSystem($foldername, $obj);

		try {
			$bReturn = $obj->createDir($foldername, $arr);
		} catch (Exception $ex) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				echo $ex->getMessage();
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->here("", 10);
			}
			/*<!-- endbuild -->*/
		}

		return $bReturn;
	}

	/**
	 * Rename an existing folder
	 * With FlySystem.
	 */
	public static function rename(string $oldname, string $newname) : bool
	{
		if (($oldname == '') && ($oldname!==$newname)) {
			return false;
		}

		$bReturn = false;
		$old = $oldname;
		$new = $newname;

		self::getFileSystem($newname, $obj);
		self::getFileSystem($oldname, $obj);

		if (self::exists($old)) {
			try {
				$bReturn = $obj->rename($oldname, $newname);
			} catch (Exception $ex) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					echo $ex->getMessage();
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->here("", 10);
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
	public static function delete(string $foldername) : bool
	{
		if ($foldername == '') {
			return false;
		}

		self::getFileSystem($foldername, $obj);
		return $obj->deleteDir($foldername);
	}

	/**
	 * Get the list of files/folders under $path, recursively or not
	 *
	 * $path should be a folder, can't be a pattern like 'file.*'
	 */
	public static function getContent(string $foldername, bool $recursive = false) : array
	{
		self::getFileSystem($foldername, $obj);
		$items = $obj->listContents($foldername, $recursive);

		return $items;
	}

}
