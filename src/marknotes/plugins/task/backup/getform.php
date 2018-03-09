<?php
/**
 * Display the backup interface
 */
namespace MarkNotes\Plugins\Task\Backup;

defined('_MARKNOTES') or die('No direct access allowed');

class Getform extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.backup';
	protected static $json_options = 'plugins.options.task.backup';

	/**
	 * Retrieve the list of folders under /docs
	 */
	private static function getFolderList() : string
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// The form will display the list of folders under
		// /docs so the user can make a full backup (/docs)
		// or a partial backup (only a subfolder)

		// Now, build the list of folders
		$arrFiles = array();
		$args=array(&$arrFiles);
		$aeEvents->loadPlugins('task.listfiles.get');
		$aeEvents->trigger('task.listfiles.get::run', $args);

		// $arrFiles contains the list of files => extract
		// folder names, make unique and sort the list
		$arrFolders = array_map('dirname', $arrFiles);
		$arrFolders = array_unique($arrFolders);
		sort($arrFolders);

		// Get the "default_folder" settings
		$docs = rtrim($aeSettings->getFolderDocs(false), DS);
		$default = self::getOptions('default_folder', $docs);

		$items = '';
		foreach ($arrFolders as $item) {
			$item = str_replace(DS, '/', $item);
			$selected = ($item === $default) ? 'selected="selected"' : '';
			$items .= '<option data-value="'.$item.'" '.$selected.'>'.
				$item.'</option>';
		}

		return $items;
	}

	/**
	 * The form contains a lot of variables and text to translate,
	 * do it here
	 */
	private static function replaceVariables(string $form) : string
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$form = str_replace('%ROOT%', rtrim($aeFunctions->getCurrentURL(), '/'), $form);

		$arr=array(
			'%BACKUP_TITLE%' => 'button_backup',
			'%BACKUP_LOG%' => 'backuplog',
			'%BACKUP_START%' => 'backup_start',
			'%BACKUP_ONLY_CAN_SEE_FOLDERS%' => 'backup_only_can_see_folders',
			'%BACKUP_THIS_FOLDER%' => 'backup_this_folder',
			'%BACKUP_LABEL_IGNORE_EXTENSIONS%' => 'backup_ignore_extensions',
			'%BACKUP_THIS_SETTINGS%' => 'backup_this_settings'
		);

		foreach ($arr as $code => $text) {
			$form = str_replace($code, $aeSettings->getText($text, ''), $form);
		}

		// Get settings
		$extensions = self::getOptions('ignore_extensions', '');
		$form = str_replace('%BACKUP_IGNORE_EXTENSIONS%', $extensions, $form);

		// Get the location of the backup folder
		$folder = $arr['folder']??'backup';
		$folder = rtrim($folder, DS);
		$folder = $aeFiles->makeFileNameAbsolute($folder);

		$txt = $aeSettings->getText('backup_folder_location', '');
		$txt = str_replace('$1', $folder, $txt);
		$form = str_replace('%BACKUP_FOLDER_LOCATION%', '<i>'.$txt.'</i>', $form);

		if (strpos($form, '%BACKUP_CBX_FOLDERS%') !== false) {
			$form = str_replace('%BACKUP_CBX_FOLDERS%', self::getFolderList(), $form);
		}

		return $form;
	}

	public static function run(&$params = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Default
		$form = '<p class="text-danger">'.
			$aeSettings->getText('not_authenticated').'</p>';

		if (self::isEnabled(true)) {
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();

			$filename = __DIR__.'/assets/form.frm';

			if ($aeFiles->exists($filename)) {

				// Get the root URL
				$root = rtrim($aeFunctions->getCurrentURL(), '/');

				$form = $aeFiles->getContent($filename);
				$form = self::replaceVariables($form);

			/*<!-- build:debug -->*/
			} else { // if ($aeFiles->exists($filename))
				// Should never occurs
				$form = '<p class="text-danger">Error - the backup interace was not found</p>';

				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("The file [".$filename."] is missing", "error");

					$form .= '<p class="text-danger">The file ['.$filename.'] is missing</p>';
				}
			/*<!-- endbuild -->*/
			}
		}

		header('Content-Type: application/json');
		echo json_encode(array('form'=>$form));

		return true;
	}
}
