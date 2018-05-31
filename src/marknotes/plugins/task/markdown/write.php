<?php

namespace MarkNotes\Plugins\Markdown;

use \Symfony\Component\Yaml\Yaml;

defined('_MARKNOTES') or die('No direct access allowed');

class Write extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.markdown.write';
	protected static $json_options = '';

	/**
	 * Add a .htaccess file in the backup folder if needed
	 * Add a .gitignore too
	 *
	 * @param  string $folder Absolute path to the backup folder
	 * @return bool
	 */
	private static function protectFolder(string $folder) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();

		if (!$aeFiles->exists($name=rtrim($folder, DS).DS.'.htaccess')) {
			// No one can access the backup folder by URL
			$content = '# marknotes - Deny access to this folder'.PHP_EOL.
				'deny from all';
			$aeFiles->create($name, $content);
		}

		// No need to add this folder in a git repository
		if (!$aeFiles->exists($name=rtrim($folder, DS).DS.'.gitignore')) {
			$content = '# Ignore everything'.PHP_EOL.'*';
			$aeFiles->create($name, $content);
		}

		return true;
	}

	/**
	 * Make a backup of the .md file before writing a newer version
	 *
	 * @param  string $filename Full path the to .md file that needs to be
	 * 					updated
	 * @return bool
	 */
	private static function makeBackup(string $filename) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Should we need to make a backup first ?
		// Take a look on the settings
		$arrSettings = $aeSettings->getPlugins('plugins.options.task.backup');

		// Take an archive before updating ?
		$make_backups = boolval($arrSettings['make_backup_before_updating']??1);

		if ($make_backups) {
			// Yes, make a backup.
			// Derive the name for the backup

			// Get the backup folder from settings.json
			// "folder" is a folder name under the web root
			// Can't be outside
			$backup_folder = $arrSettings['folder']??'';

			if (realpath($backup_folder) == FALSE) {
				// Get the full path to the root
				$root = $aeSettings->getFolderWebRoot();

				if (!($aeFunctions->startsWith($backup_folder, $root))) {
					$backup_folder = $root.$backup_folder;
				}

				// The folder will be something like
				// C:\webfolder\Notes\.backups
				$backup_folder = str_replace('/', DS, $backup_folder);
			} else {
				$backup_folder = realpath($backup_folder);
			}

			// Create the backup folder if needed
			if (!$aeFolders->exists($backup_folder)) {
				$aeFolders->create($backup_folder);
			}

			// Add a .htaccess file in the backup folder if needed
			// Add a .gitignore too
			self::protectFolder($backup_folder);

			$root = $aeSettings->getFolderWebRoot();
			$backup_file = $backup_folder.DS.str_replace($root, '', $filename);

			if (!$aeFolders->exists(dirname($backup_file))) {
				$aeFolders->create(dirname($backup_file));
			}

			// Get the timestamp (like "20180528_092700")
			$datetime = new \DateTime();
			// International format
			$stamp=$datetime->format('Y\-m\-d\_His');

			// Get the full backup filename, like
			// C:\webfolder\Notes\.backups\folder\sub\note_2018-05-28_092854.md
			$backup_file = str_replace('.md', '_'.$stamp.'.md', $backup_file);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('Make a backup of the file', 'debug');
				$aeDebug->log('Create '.$backup_file, 'debug');
			}
			/*<!-- endbuild -->*/

			// And write the file
			$oldContent = $aeFiles->getContent($filename);
			$aeFiles->rewrite($backup_file, $oldContent);

		} // if ($make_backups)

		return true;
	}

	public static function run(&$params = null) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Retrieve the filename from the session
		$filename = $aeSession->get('filename', '');

		// Be sure to have the .md extension
		$filename = $aeFiles->removeExtension($filename).'.md';

		if ($filename !== '') {

			// Get the absolute filename on the disk
			if (!$aeFunctions->startsWith($filename, $aeSettings->getFolderDocs(true))) {
				$filename = $aeSettings->getFolderDocs(true).$filename;
			}

			$content = trim($params['markdown']);

			// Add the YAML block if allowed by settings.json
			$aeEvents = \MarkNotes\Events::getInstance();
			$params['markdown'] = $content;
			$params['yaml'] = '';
			$aeEvents->loadPlugins('markdown.yaml');
			$args = array(&$params);
			$aeEvents->trigger('markdown.yaml::markdown.read', $args);
			$html = $args[0]['markdown'];

			// Check if there is a YAML header and if so,
			// add in back in the .md file
			if ($params['yaml']!=='') {
				$yaml = $aeSession->get('yaml').PHP_EOL;

				// Should use $html and not $content here below
				// $html is the note's content; without the YAML block
				// (removed by markdown.yaml::markdown.read)
				// So, add the YAML block back
				$content = "---".PHP_EOL.$yaml."---".PHP_EOL.
					PHP_EOL.$html;
			}

			// Keep an archive of the note before saving the new version
			self::makeBackup($filename, $content);

			// And write the file
			$aeFiles->rewrite($filename, $content);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('Rewrite DONE '.$filename, 'debug');
			}
			/*<!-- endbuild -->*/
		} else {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->here('Event markdown.write - Session invalid, no filename found', 5);
			}
			/*<!-- endbuild -->*/
		} // if ($filename !== '')

		return true;
	}
}
