<?php
/**
 * GetFolders - Return the HTML of the restrict_folder form
 */
namespace MarkNotes\Plugins\Task\Search;

defined('_MARKNOTES') or die('No direct access allowed');

class Getfolders extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.search';
	protected static $json_options = 'plugins.options.task.search';

	/**
	 * Return the code for showing the login form and respond
	 * to the login action
	 */
	public static function run(&$params = null) : bool
	{
		$form = '';

		if (self::isEnabled(true)) {
			// Ok, the login task is enabled
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$filename = __DIR__.'/assets/getfolders.frm';

			if ($aeFiles->exists($filename)) {
				// Get the root URL
				$root = rtrim($aeFunctions->getCurrentURL(), '/');

				$form = $aeFiles->getContent($filename);

				$form = str_replace('%ROOT%', rtrim($aeFunctions->getCurrentURL(), '/'), $form);
				$form = str_replace('%SEARCH_DEFINE%', $aeSettings->getText('search_define_folder', 'Restrict to this folder (and subfolders)'), $form);
				$form = str_replace('%SEARCH_REMOVE%', $aeSettings->getText('search_remove_folder', 'Remove the restriction'), $form);

				// Now, build the list of folders
				$arrFiles = array();
				$args=array(&$arrFiles);
				$aeEvents->loadPlugins('task.listfiles.get');
				$aeEvents->trigger('task.listfiles.get::run', $args);

				// $arrFiles contains the list of files => extract
				// folder names, make unique and sort the list
				$arrFolders = array_map('dirname', $arrFiles);
				$arrFolders = array_unique($arrFolders);
				natcasesort($arrFolders);

				$values = '';
				foreach ($arrFolders  as $key => $value) {
					$values .= '<option value="'.$key.'">'.$value.'</option>';

				}

				$form = str_replace('%FOLDERS%', $values, $form);

			/*<!-- build:debug -->*/
			} else { // if ($aeFiles->exists($filename))
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("The file [".$filename."] is missing", "error");
				}
			/*<!-- endbuild -->*/
			} // if ($aeFiles->exists($filename))
		}

		header('Content-Type: application/json');
		echo json_encode(array('form'=>$form));

		return true;
	}
}
