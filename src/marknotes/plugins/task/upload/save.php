<?php
/**
 * Upload - Receive a file and save it
 * @link : http://www.dropzonejs.com
 */
namespace MarkNotes\Plugins\Task\Upload;

defined('_MARKNOTES') or die('No direct access allowed');

class Save
{
	/**
	 * Generate the code for the upload form
	 */
	public static function run(&$params = null)
	{
		// Only if a file was indeed sent to the server
		if (!empty($_FILES)) {
			$aeFolders = \MarkNotes\Folders::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			// Retrieve the folder where to save the files
			// and make it absolute (f.i. C:\marknotes\docs\folder\)
			$docs = $aeSettings->getFolderDocs(true);
			$base64 = trim(rawurldecode($aeFunctions->getParam('folder', 'string', '', false)));

			// decode
			$folder = base64_decode($base64);

			// Make the path absolute
			if (!$aeFunctions->startsWith($folder , $docs)) {
				$folder = $docs.$folder;
			}

			$folder = str_replace('/', DS, $folder);

			// Be sure to have the final slashes
			$folder = rtrim($folder, DS).DS;

			// Get the temporary filename, once uploaded
			// Something like c:\wamp64\tmp\php35FC.tmp
			$tempFile = $_FILES['file']['tmp_name'];

			// When the user has dropped an entire folder on the DropZone
			// area (like a /images folder), the treeview.js script
			// (function fnPluginTaskTreeView_upload_droparea) has then
			// added a second parameter : the relativeName.
			// That item IS NOT PRESENT when the user has dropped
			// a single file or files not included in a folder (i.e.
			// the user has only select files, no folder)
			//
			// So if a folder has been selected, relativeName will f.i.
			// contains images/banner.png
			//
			// If relativeName is missing, retrieve the name from
			// $_FILES['file']['name'] (which never contains a foldername,
			// just the file basename (like file.md)
			$relativeName = trim(rawurldecode($aeFunctions->getParam('relativeName', 'string', $_FILES['file']['name'], false)));

			$relativeName = str_replace('/', DS, $relativeName);

			// Retrieve the original name
			// Like note.md
			if (!$aeFolders->exists(dirname($folder.$relativeName))) {
				// Create the folder and, if needed, create subfolders
				// too (recursive)
				$aeFolders->create(dirname($folder.$relativeName));
			}

			$targetFile = str_replace('/', DS, $folder.$relativeName);

			// Move the file to the target folder and rename it
			// back to its original name
			move_uploaded_file($tempFile, $targetFile);
		}

		return true;
	}

	/**
	 * Attach the function and responds to events
	 */
	public function bind(string $task)
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->bind('run', __CLASS__.'::run', $task);
		return true;
	}
}
