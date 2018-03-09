<?php
/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!!!! DON'T UPDATE THIS LIBRARY SINCE HAS BEEN A !!!!!!!
 * !!!!!!!  LOT  MODIFIED  TO  WORK  WITH  MARKNOTES  !!!!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 * Original author :
 * 		@author Denis Alexandrov <stm.switcher@gmail.com>
 * 		@date 12.12.2015 14:23:10
 * 		@src https://github.com/stmswitcher/ajax-grabber
*/

namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');
define('MB', 1048576);

class Grabber
{
	const OPTIONS = 'plugins.options.task.backup';

	/** @var \ZipArchive Archive to work with */
	private $archive;

	// Name of the folder where to store ZIP files
	// Can be adjusted in the plugin options
	private $backup_folder = 'backup';

	// Name of the ZIP file that will contain the backup
	// Stored in the backup_folder
	private $zip_filename = '';
	private $zip_prefix = '%DATE%_';

	// List of files to store in the archive
	private $files = '';

	// Number of files to process
	private $files_count = 0;

	// Maximum number of files to process in one call
	private $max_count_files = 50;

	// When the addfile task is called, severall files will
	// be processed in one call (f.i. 15 files by calls)
	// But when these files are big ones, don't process a
	// given number of files (don't process 15) but until a max
	// size is reached (f.i. process max 15 files but having a max
	// total size of 5 MB f.i.)
	// files_size is in bytes
	private $files_size = 0;

	// Maximum size to process in one call : 5MB
	// If just one file is bigger than this size, only one file will
	// be processed but when the sum of 14 files is just bigger,
	// then process 14 files (and not 15)

	private $max_size = 10 * MB;

	// The current processed file number
	private $offset = 0;

	// Root folder of the web application
	private $root = '';

	/**
	 * List of bootstrap classes and short associations
	 * @var array
	 */
	private static $BUTTON_CLASSES = [
		'error' => 'btn-warning',
		'wait'  => 'btn-default',
		'complete' => 'btn-success',
		'error' => 'btn-danger',
	];

	/**
	 * Class constructor
	 * Here we'll only check for PHP session and try to
	 * set class' offset.
	 */
	public function __construct()
	{
		try {
			$this->checkSession();

			// Retrieve the offset i.e. the filename to process
			$this->setOffsetFromGet();

			$aeSettings = \MarkNotes\Settings::getInstance();
			$arr = $aeSettings->getPlugins(self::OPTIONS);

			// Get the root folder
			$this->root = $aeSettings->getFolderWebRoot();

			// Get the location of the backup folder
			$this->backup_folder = $arr['folder']??'backup';
			$this->backup_folder = rtrim($this->backup_folder, DS);
			// And make the folder name absolute
			$this->backup_folder = $this->root.$this->backup_folder.DS;

			// Get the prefix to use for naming ZIP file
			$this->zip_prefix = $arr['prefix']??'%DATE%_';

			// max number of files to process at once
			$this->max_count_files = $arr['max_count_files']??50;

			// max total size (in MB) to process at once
			$this->max_size = intval($arr['max_size_files']??10);
			$this->max_size = $this->max_size * MB;

		} catch (\Exception $ex) {
			$this->error($ex);
		}
	}

	/**
	 * Render results of the script's work
	 * @param bool $end Script finished it's work
	 * @param string $log_info Info to be outputed into AJAX-log
	 * @param string $btn_text Text to set to primary button
	 * @param string $btn_class Class to set to primary button {@see self::$BUTTON_CLASSES}
	 * @param int $offset New offset to return to JavaScript
	 */
	private function outputResult($end, $log_info, $btn_text, $btn_class, $offset) : array
	{
		$btn_bootstrap_class = self::$BUTTON_CLASSES[$btn_class];

		return compact('end', 'log_info', 'btn_text', 'btn_bootstrap_class', 'offset');
	}

	/**
	 * Return error message and exit with status = 1
	 * @param \Exception $ex
	 * @param bool $fatal if script shouldn't proceed
	 */
	private function error(\Exception $ex, $fatal = true)
	{
		$message = $ex->getCode().': '.$ex->getMessage();

		$this->outputResult($fatal, $message, $fatal ? 'Error' : 'Skipping', 'error', $fatal ? 0 : ++$this->offset);

		if ($fatal && $this->archive instanceof \ZipArchive) {
			$this->archive->close();
		}

		exit(1);
	}

	/**
	 * Define the name of the ZIP file
	 */
	private function buildFilename() : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		if (!$aeFolders->exists($this->backup_folder)) {
			// Create the folder
			$aeFolders->create($this->backup_folder);

			// No one can access the backup folder by URL
			$content = 'deny from all';
			$aeFiles->create($this->backup_folder.'.htaccess', $content);
		}

		$file = $aeSession->get('backup_zipfilename', '');

		if ($file == '') {
			// The zip filename is not yet defined

			// Get the Now() date so we can replace %DATE% in the
			// prefix
			//		Y	= 2018
			//		m	= 12
			// 		d	= 24
			//		H	= 23
			//		i	= 59
			//		s	= 00
			$dte = date("YmdHis", time());

			$this->zip_prefix = str_replace('%DATE%', $dte, $this->zip_prefix);

			// Add an underscore after the prefix and be sure there
			// is only one
			$this->zip_prefix = rtrim($this->zip_prefix, '_');

			// Get the suffix (_all for the entire /docs folder)
			// or the basename of the last subfolder to save
			$suffix = $aeSession->get('backup_suffix', '');

			// Derive the filename - Use the prefix and add an ID
			// based on the session ID so the filename will be unique
			$id = substr(sha1(session_id()), 0, 8);
			$file = $this->zip_prefix.$suffix.'_'.$id.'.zip';

			$aeSession->set('backup_zipfilename', $file);
		}

		return $file;
	}

	/**
	 * Check if PHP session was started
	 * @throws \Exception If no PHP session
	 */
	private function checkSession() : bool
	{
		switch (session_status()) {
			case PHP_SESSION_DISABLED:
				throw new \Exception("PHP sessions disabled. Unable to start the script.", 500);
			case PHP_SESSION_NONE:
				throw new \Exception("Session must be started before initializing script.", 400);
			default:
				return true;
		}
	}

	/**
	 * Try to set offset from GET param
	 * @throws Exception If no GET param 'offset' present
	 */
	private function setOffsetFromGet() : bool
	{
		// Get the "offset" variable from the query string
		// Make sure it's a number
		$this->offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);

		if (is_null($this->offset)) {
			throw new \Exception('Unable to get offset', 424);
		}

		return true;
	}

	/**
	 * Remove file if offset equals 0
	 */
	private function checkOffsetAndUnlinkFile() : bool
	{
		// offset = 0 => we're starting the process
		if ($this->offset === 0) {
			$this->unlinkFileIfPresent();
		}

		return true;
	}

	/**
	 * Check if archive file is present and try to remove it.
	 * @throws \Exception If unable to remove file
	 */
	private function unlinkFileIfPresent() : bool
	{
		$file = $this->backup_folder.$this->zip_filename;

		$aeFiles = \MarkNotes\Files::getInstance();
		if ($aeFiles->exists($file)) {
			$result = $aeFiles->delete($file);
			if (!$result) {
				throw new \Exception('Unable to remove existing archive file '.$file, 403);
			}
		}

		return true;
	}

	/**
	 * Initialize archive
	 * Create new file if offset equals zero
	 * @return \ZipArchive
	 */
	private function initArchive() : bool
	{
		$this->archive = new \ZipArchive();
		$flags = null;

		// Create the ZIP only when we're starting the process
		// i.e. when processing file #0
		if ($this->offset == 0) {
			$flags = \ZipArchive::CREATE;
		}

		$file = $this->backup_folder.$this->zip_filename;

		return $this->archive->open($file, $flags);
	}

	/**
	 * Reads the list of files to archive and get the count of them
	 */
	private function readInput() : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// The list of files is stored in a file by the
		// task.backup.getfiles class. That filename is
		// saved in the session so the file is created only
		// once by session for optimization purposes.
		$file = $aeSession->get('backup_filename', '');

		if ($file !== '') {
			// The file has been stored in the /tmp folder
			$file = $aeSettings->getFolderTmp().$file;

			if ($aeFiles->exists($file)) {
				$this->files = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				$this->files_count = sizeof($this->files);
			}
		} else {
			throw new \Exception("The task.backup.getfiles task has not be fired before", 305);
		}

		return true;
	}

	/**
	 * Finish archiving and return filepath within primary button
	 */
	private function completeArchive() : bool
	{
		$this->archive->close();
		$this->outputResult(true, 'Archive ready', 'Download', 'complete', 0);
		return true;
	}

	/**
	 * Processing current file
	 *
	 * If current offset is equal to number of files in
	 * input we're closing archive and sending download link.
	 *
	 * For each file we have, get contents with file_get_contents
	 * and add the file to archive.
	 *
	 * At the end of this method offset will be incremented.
	 *
	 * @throws \Exception if were unable to get data
	 */
	private function processFile(int $offset) : array
	{
		if ($offset >= $this->files_count) {
			// It's done when offset is equal to the total number
			// of files to process
			$this->completeArchive();
			return true;
		}

		// Get the file to process
		$filename = $this->files[$offset];

		// and get his content
		$data = file_get_contents($filename);

		// $filename is absolute, make it relative to the root
		// folder so, when uncompressing the ZIP file back in the
		// root, files will be extracted in the correct folder
		$basename = str_replace($this->root, '', $filename);

		// Add the file in the archive
		$this->archive->addFile($filename, $basename);

		// And return to the Ajax task a log info
		$end = ($offset+1 >= $this->files_count);

		$btn_text = ($offset + 1) . "/". $this->files_count;
		$btn_class = ($end ? 'complete' : 'wait');
		$next_offset = ($end ? 0 : ++$offset);
		$log_info = $btn_text.' - '.$basename.' - success';

		// Show "Download" at the end
		if ($end) {
			$btn_text = 'Download';
		}

		$arr = $this->outputResult($end, $log_info, $btn_text, $btn_class, $next_offset);

		return $arr;
	}

	/**
	 * Main method sequence which will initialize archive
	 * and process files.
	 */
	public function process() : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		try {

			// Derive the name of the ZIP file to create
			$this->zip_filename = $this->buildFilename();

			// If offset is equal to zero
			// (the script runs for the first time) check
			// if file exists and try to remove it.
			$this->checkOffsetAndUnlinkFile();

			// Initialize archive
			// (creating a new one or opening existing)
			$this->initArchive();

			// Get the list of files to archive
			$this->readInput();

			// Processing current file and close archive

			$json = array();

			// Maximum number of files to process in one call
			$j = $this->offset + $this->max_count_files;

			// If $j is bigger then files_count, just use
			// that number (i.e. we are processing the last
			// files)
			if ($j > $this->files_count) {
				$j = $this->files_count;
			}

			// Total number in bytes processed by this current
			// process() task.
			$this->files_size = 0;

			for ($i = $this->offset; $i < $j; $i++) {

				// Get the name of the file that will be processed
				$filename = $this->files[$i];

				// and his filesize
				$filesize = $aeFiles->getSize($filename);

				if ($this->files_size == 0) {
					// file_size = 0 : this is the first file
					// to process so ... process it and add the
					// file in the ZIP file
					$json[] = $this->processFile($i);

					// Sum the size of all processed files
					$this->files_size +=  $filesize;
				} else {
					// Process the file only if we stay below the
					// max. limit
					if ($this->max_size >= ($this->files_size + $filesize)) {
						$json[] = $this->processFile($i);
						$this->files_size += $filesize;
					} else {
						// We're above the limit, stop this
						// task.
						break;
					}
				}
			}

			// Close the archive otherwise will be locked
			// for the next file
			$this->archive->close();
		} catch (\Exception $ex) {
			$this->error($ex, false);
		}

		header('Content-Type: application/json');
		echo json_encode($json, JSON_PRETTY_PRINT);

		exit(0);

		return true;
	}
}
