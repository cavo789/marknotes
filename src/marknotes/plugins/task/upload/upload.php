<?php
/**
 * Upload - Display a drop area to let the user to drag&drop multiple
 * files
 * @link : http://www.dropzonejs.com
 */
namespace MarkNotes\Plugins\Task\Upload;

defined('_MARKNOTES') or die('No direct access allowed');

class Drop
{
	/**
	 * Generate the code for the upload form
	 */
	public static function run(&$params = null)
	{
		$form = '<h1>'.__FILE__.' - '.__LINE__.'</h1>';

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$docs = $aeSettings->getFolderDocs(true);
		$root = $aeSettings->getFolderWebRoot();

		// Get the relative foldername (f.i. /folder/)
		$base64 = trim(rawurldecode($aeFunctions->getParam('param', 'string', '', false)));
		$folder = base64_decode($base64);

		if ($folder !=='') {
			// Get the full name (absolute and no more relative)
			// f.i. C:\marknotes\docs\folder\
			$folder = str_replace('/', DS, $docs.$folder);

			// For the display, don't show the full foldername
			// but only f.i. docs/upload_folder
			$folder = str_replace($root, '', $folder);

			$form = '<h2>Import in '.$folder.'</h2>';
			$form.= '<form class="dropzone" id="upload_droparea">'.
				'<input type="hidden" name="folder" value="'.$base64.'">'.
				'</form>';

			header('Content-Transfer-Encoding: ascii');
			header('Content-Type: text/html; charset=utf-8');
			echo $form;
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
