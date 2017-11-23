<?php
/**
 * When exporting to pdf, txt, docx, ... before the exportation,
 * if the debug mode is set, delete the existing exported file if present.
 *
 * So if my_note.docx exists, that file will be deleted so the export code
 * run again. Usefull when coding but not during normal use.
 *
 * Only active when debug=1 in settings.json
 */
namespace MarkNotes\Plugins\Task\Export\Before;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(dirname(__DIR__)).'/.plugin.php');

class Debug extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.before.debug';
	protected static $json_options = '';

	/*<!-- build:debug -->*/
	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();

		// Retrieve the filename and add the .html extension
		$filename = $params['filename'] ?? '';
		$filename = $aeFiles->removeExtension($filename).'.html';

		// Derive the name of the file and set it as an absolute filename
		if ($filename!=='') {
			$filename = $aeFiles->removeExtension($filename).'.'.$params['extension'];
			$filename = $aeSettings->getFolderDocs(true).$filename;
		}

		// Never kill .md files !!!
		if ($params['extension']!=='md') {
			if ($aeFiles->fileExists($filename)) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("Debug mode set, kill the previous ".
				$filename." so a new version will be created", "debug");

				// During the development mode, always recreate the file
				try {
					unlink(utf8_decode($filename));
				} catch (Exception $e) {
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log($e->getMessage(), "error");
					}
				}
			} // if ($aeFiles->fileExists($filename))
		} // if ($params['extension']!=='md')
		return true;
	}
	/*<!-- endbuild -->*/

	/**
	 * Verify if the plugin is well needed and thus have a reason
	 * to be fired
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = false;

		/*<!-- build:debug -->*/
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// Only when debugmode is set
			$aeSettings = \MarkNotes\Settings::getInstance();
			$bCanRun = $aeSettings->getDebugMode();
		}
		/*<!-- endbuild -->*/

		return $bCanRun;
	}
}
