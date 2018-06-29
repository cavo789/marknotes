<?php
/**
 * Upload - Receive a file and save it
 * @link : http://www.dropzonejs.com
 */
namespace MarkNotes\Plugins\Task\Upload;

defined('_MARKNOTES') or die('No direct access allowed');

class Save
{

	protected static $json_options = 'plugins.options.task.upload';

	/**
	 * Check that the uploaded file has an allowed MIME type
	 * @param  [type]  $filename [description]
	 * @return array
	 * 		First entry, a boolean (Yes if the mime is allowed)
	 * 		A string : the detected mime
	 */
	private static function checkAllowedMime(string $filename, string $ext) : array
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the list of allowed mime types
		$arr = $aeSettings->getPlugins(self::$json_options);
		$arrUploadMIME = $arr['accept_upload_mime']??array();

		// Get the mimetype of the uploaded file
		$helpers = $aeSettings->getFolderAppRoot();
		$helpers .= 'marknotes/plugins/task/upload/helpers/';
		$helpers = str_replace('/', DS, $helpers);

		require_once($helpers.'upload.php');

		$aeHelpers = new Helpers\Upload();
		$mime = $aeHelpers->detectMimeType($filename, $ext);
		unset($aeHelpers);

		$bAllowed = false;

		// $mime can be f.i. "text/csv", check if that
		// type is listed in our $arrUploadMIME array and
		// if so, bingo, it's allowed
		if (in_array($mime, $arrUploadMIME)) {
			$bAllowed = true;
		}

		if (!$bAllowed) {
			// The $mime is perhaps "image/png" so check
			// if the first part "image" is allowed
			$tmp = explode('/', $mime);
			$type = $tmp[0];
			if (in_array($type, $arrUploadMIME)) {
				$bAllowed = true;
			}
		}

		return array($bAllowed, $mime);

	}

	/**
	 * Derive the name of the folder when the uploaded
	 * file will be saved
	 *
	 * @return string
	 */
	private static function getFolderName(bool $isImage) : array
	{
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Retrieve the folder where to save the files
		// and make it absolute (f.i. C:\marknotes\docs\folder\)
		$docs = $aeSettings->getFolderDocs(true);
		$folder = trim(rawurldecode($aeFunctions->getParam('folder', 'string', '', false)));

		// and decode
		$folder = base64_decode($folder);

		// Make the path absolute
		if (!$aeFunctions->startsWith($folder , $docs)) {
			$folder = $docs.$folder;
		}

		// $folder contains the root folder where to save
		// f.i."C:\marknotes\docs\folder\"
		// Be sure to have the final slashes
		$folder = rtrim(str_replace('/', DS, $folder), DS).DS;

		// And get the subfolder (by default, the name of the
		// edited note if called from the dropzone area of the editor)
		$subFolder = trim(rawurldecode($aeFunctions->getParam('subfolder', 'string', '', false)));
		if ($subFolder!=='') {
			$subFolder = base64_decode($subFolder);
		}
		// If the uploaded file is an image, store the file in
		// the .images folder or, otherwise, in the .files folder
		$subFolder = ($isImage ? '.images' : '.files').DS.$subFolder.DS;

		$folder = rtrim($folder.$subFolder, DS).DS;

		return array($folder, $subFolder);
	}

	/**
	 * Generate the code for the upload form
	 */
	public static function run(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = '';
		$type = '';
		$tag = '';
		$height = '';
		$width = '';
		// Only if a file was indeed sent to the server
		if (!empty($_FILES)) {

			// Get the temporary filename, once uploaded
			// Something like c:\wamp64\tmp\php35FC.tmp
			$tempFile = $_FILES['file']['tmp_name'];

			// Get the extension of the file that will be
			// created if the upload can be done
			$ext = trim(rawurldecode($aeFunctions->getParam('relativeName', 'string', $_FILES['file']['name'], false)));
			$ext = $aeFiles->getExtension($ext);

			list($bAllowed, $mime) = self::checkAllowedMime($tempFile, $ext);

			if ($bAllowed) {

				// Image or file ?
				$isImage = false;
				if ($aeFunctions->startsWith($mime, 'image/')) {
					$isImage = true;
				}

				list($folder, $subFolder) = self::getFolderName($isImage);

				// When the user has dropped an entire folder on the DropZone
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
				$aeFolders = \MarkNotes\Folders::getInstance();

				if (!$aeFolders->exists(dirname($folder.$relativeName))) {
					// Create the folder and, if needed, create subfolders
					// too (recursive)
					$aeFolders->create(dirname($folder.$relativeName));
				}

				$targetFile = str_replace('/', DS, $folder.$relativeName);

				// Move the file to the target folder and rename it
				// back to its original name
				move_uploaded_file($tempFile, $targetFile);

				$status = 1;
				$message = 'Success';

				$type = ($isImage ? 'image' : 'file');

				if ($isImage) {
					list($width, $height, $tmp, $attr) = getimagesize($targetFile);
				}

				// Return the name of the uploaded file
				// but relative to the docs folder
				// (like folder/subfolder/.images/image.png)
				$root = $aeSettings->getFolderDocs();
				$file = str_replace($root, '', $targetFile);
				$file = str_replace(DS, '/', $file);

				$basename=basename($aeFiles->removeExtension($file));

				$url = $subFolder.$relativeName;
				$url = str_replace(DS, '/', $url);

			} else { // if ($bAllowed)

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("The uploaded file isn't allowed, the ".
						"mimetype [".$mime."] is not in a whitelist","debug");
				}
				/*<!-- endbuild -->*/

				$filename = trim(rawurldecode($aeFunctions->getParam('relativeName', 'string', $_FILES['file']['name'], false)));

				$message = $aeSettings->getText('error_mime_not_allowed');
				$message = str_replace('$1', $filename, $message);
				$message = str_replace('$2', $mime, $message);
				$file = $filename;
				$basename = basename($filename);
				$status = 0;

			} // if ($bAllowed)
		} // if (!empty($_FILES))

		header('Content-Type: application/json');
		echo json_encode(array(
			'status' => $status,
			'file' => $file,
			'basename' => $basename,
			'url' => $url,
			'type' => $type,
			'height' => $height,
			'width' => $width,
			'mime' => $mime,
			'message' => $message));

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
