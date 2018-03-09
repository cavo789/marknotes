<?php
/**
 * Get the list of files to archive
 */
namespace MarkNotes\Plugins\Task\Backup;

defined('_MARKNOTES') or die('No direct access allowed');

class Getfiles extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.backup';
	protected static $json_options = '';

	/**
	 * $arr is an array filled in by FlySystem and contains all
	 * files under /docs (also images, pdf, ...)
	 *
	 * onlyCanSee will restrict the folders to only those accessible
	 * by the visitor so, if ACLs is loaded, remove any disallowed
	 * folder i.e. folder not accessible to the logged in user
	 */
	private static function onlyCanCee(array $arr) : array
	{
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the full path to the /docs folder
		$docs = $aeSettings->getFolderDocs(true);

		$arrFiles = array();
		foreach ($arr as $file) {
			if ($file['type']=='file') {
				$arrFiles[] = str_replace('/', DS, $docs.$file['path']);
			}
		}

		// $bACLsLoaded will be set true if at least
		// one folder is protected
		$bACLsLoaded = boolval($aeSession->get('acls', '') != '');

		if ($bACLsLoaded) {
			$aeEvents = \MarkNotes\Events::getInstance();

			// Run the filter_list task to remove any protected
			// files not allowed for the current user

			$aeEvents->loadPlugins('task.acls.filter_list');
			$args=array(&$arrFiles);
			$aeEvents->trigger('task.acls.filter_list::run', $args);

			// Retrieve the filtered array i.e. that Files
			// well accessible to the current user
			$arrFiles=$args[0];
		} // if ($bACLsLoaded)

		return $arrFiles;
	}

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$files = '';

		if (self::isEnabled(true)) {
			$root = $aeSettings->getFolderWebRoot();

			// Retrieve the folder to archive
			$folder = trim(urldecode($aeFunctions->getParam('folder', 'string', '', true)));
			$folder = trim($folder, '"');

			if (($folder=='') || ($folder==rtrim($aeSettings->getFolderDocs(false), DS))) {
				// Process the /docs or "" folder =>
				// it's a full backup
				$folder = $aeSettings->getFolderDocs(false);

				// Get files for /docs ==> full backup
				$suffix = '_full';
			} else {
				// Not for /docs ==> partial backup
				// Get the last folder for the suffixe
				// So if $folder is "docs/folder/subfolder1/sub2"
				// then use "sub2" for the suffix
				$suffix = '_'.$aeFiles->sanitize(basename($folder));
			}

			// Make the folder name absolute
			$folder = $aeFiles->makeFileNameAbsolute($folder);

			// Generate the name of the temporary file that
			// will contains the list of files that should be
			// processed during the backup
			$id = substr(sha1(session_id()), 0, 8);
			$filename = 'backup'.$suffix.'_'.$id.'.txt';
			$aeSession->set('backup_filename', $filename);
			$aeSession->set('backup_suffix', $suffix);

			// Reset the session backup_zipfilename variable
			// since we're perhaps creating a new file (in case
			// of a second run but for a another folder)
			$aeSession->set('backup_zipfilename', '');

			// The file will be stored in the /tmp folder
			$filename = $aeSettings->getFolderTmp().$filename;

			// Get the list of all files under the /docs folder
			// (all = not only .md but everything)
			// Note : $folder can be a subfolder of /docs like
			// 		/docs/subfolder/folder_to_save
			$arr = $aeFolders->getContent($folder, true);

			// $arr contains all files but, if somes folders are
			// restricted thanks the ACLs plugin, these folders
			// should not be in the list so, remove them and keep
			// only folder that the current logged-in user can see
			$arrFiles = self::onlyCanCee($arr);

			// Always backup these files
			$files .= $root.'.htaccess'.PHP_EOL;
			$files .= $root.'settings.json'.PHP_EOL;

			// Add any files in the $files variable
			foreach ($arrFiles as $key=>$file) {
				$files.=$file.PHP_EOL;
			}

			// And create the file
			$aeFiles->create($filename, $files);

			// Return 1 or 0 depending on the presence of the file
			$status = $aeFiles->exists($filename)?1:0;
			$message =  $aeSettings->getText($status ? 'backup_getfiles_ok' : 'backup_getfiles_error');
		} else { // if (self::isEnabled(true))
			$status = 0;
			$message = $aeSettings->getText('not_authenticated');
		} // if (self::isEnabled(true))

		header('Content-Type: application/json');
		echo json_encode(array('status'=>$status,'message'=>$message));

		return true;
	}
}
