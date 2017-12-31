<?php
/**
 * Once the file has been exported (f.i. the note /docs/folder/note.txt has
 * been created), the copy plugin will allow to copy the file automatically
 * elsewhere (in an another folder f.i.).
 *
 * The path should be specified in the settings.json Files, in the plugins->options
 * node, like this :
 *
 *  	"options": {
 *			"task": {
 *				"export": {
 *				 	"after": {
 *				 		"copy": {
 *				 			"target_folder": "c:\\temp\\marknotes"
 *				 		}
 *				 	}
 *				}
 *			}
 *		}
 */
namespace MarkNotes\Plugins\Task\Export\After;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(dirname(__DIR__)).'/.plugin.php');

class Copy extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.after.copy';
	protected static $json_options = 'plugins.options.task.export.after.copy';

	private static function debugMsg(string $msg, string $type = 'error') : bool
	{
		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log($msg, $type);
		}
		/*<!-- endbuild -->*/

		return true;
	}

	public static function run(&$params = null) : bool
	{
		$bReturn = false;

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();

		$filename = $params['output'] ?? '';   // output filename (fullname)

		if ($aeFiles->exists($filename)) {
			// Get the folder where the file should be copied
			$target_folder = self::getOptions('target_folder', '');

			if ($target_folder!=='') {
				$target_folder=rtrim($target_folder, DS).DS;

				if ($aeFolders->exists($target_folder)) {
					$filename = $params['output'];

					self::debugMsg('Copying ['.$filename.'] to '.
					   $target_folder, 'debug');

					try {
						copy($filename, $target_folder.basename($filename));
					} catch (Exception $e) {
						self::debugMsg($e->getMessage());
					}

					$bReturn = true;
				} else { // if ($aeFolders->exists($target_folder))
					// Error ! The specified folder doesn't exists

					self::debugMsg('Invalid settings.json, the folder '.
					'['.$target_folder.'] is invalid, please verify your '.
					static::$json_options.' setting', 'error');
				} // if ($aeFolders->exists($target_folder))
			} else { // if ($target_folder!=='')
				self::debugMsg('Invalid settings.json, you need to specify '.
				   'a valid foldername in '.static::$json_options, 'debug');
			} // if ($target_folder!=='')
		}

		return $bReturn;
	}

	/**
	 * Verify if the plugin is well needed and thus have a reason
	 * to be fired
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// Get plugin's options
			$copyFolder = self::getOptions('target_folder', '');
			if ($copyFolder !== '') {
				// A target folder has been mentionned, the plugin
				// can be fired
				$bCanRun = true;
			}
		}

		return $bCanRun;
	}
}
