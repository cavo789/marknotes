<?php
/**
 * image_gallery - Get the modal
 */
namespace MarkNotes\Plugins\Task\Image_Gallery;

defined('_MARKNOTES') or die('No direct access allowed');

class GetModal extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.image_gallery';
	protected static $json_options = '';

	/**
	 * Return the code for showing the login form and respond to
	 * the login action
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$filename = __DIR__.'/assets/modal.frm';

		if ($aeFiles->exists($filename)) {
			// Get the root URL
			$root = rtrim($aeFunctions->getCurrentURL(), '/');

			$form = $aeFiles->getContent($filename);

			$close = $aeSettings->getText('close', 'close');

			$form = str_replace('%CLOSE%', $close, $form);

		/*<!-- build:debug -->*/
		} else { // if ($aeFiles->exists($filename))
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("The file [".$filename."] is missing", "error");
			}
		/*<!-- endbuild -->*/
		} // if ($aeFiles->exists($filename))

		header('Content-Type: application/json');
		echo json_encode(array('form'=>$form));

		return true;
	}
}
