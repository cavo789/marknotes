<?php
/**
 * Image - Get image size
 *
 * This task is called by the editor, when a new image has been
 * uploaded. The idea is to retrieve the height and the width of
 * of the image so these info will be used in the ![]() tag.
 *
 * Not really needed but usefull so the author can see the height and
 * width and can immediatly change them (even if not really optimized)
 *
 * Answer to URL like index.php?task=task.image.getsize&file=joomla.png&note=foldername
 */
namespace MarkNotes\Plugins\Task\Image;

defined('_MARKNOTES') or die('No direct access allowed');

class GetSize
{
	/**
	 * Generate the code for the upload form
	 */
	public static function run(&$params = null)
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$docs = $aeSettings->getFolderDocs(true);

		// Note is f.i. /docs/folder/note_name.
		$note = trim($aeFunctions->getParam('note', 'string', '', false));
		// Retrieve the folder name (i.. /docs/folder)
		$folder = rtrim(dirname($note), DS);

		$file = trim($aeFunctions->getParam('file', 'string', '', false));

		$file = str_replace('/', DS, $docs.$folder.'/.images/'.$file);

		$arrReturn = array();

		if (file_exists($file)) {
			// Get image size and return this info
			list($width, $height, $type, $attr) = getimagesize($file);
			$arrReturn['width'] = $width;
			$arrReturn['height'] = $height;
		}

		header('Content-Type: application/json');
		echo json_encode($arrReturn);;

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
