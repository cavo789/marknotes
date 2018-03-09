<?php
/**
 * This task will received, as parameter, an array with filenames
 * and will remove from that array any files located in a
 * protected folder when the connected user isn't allowed
 *
 * The array will be something like :
 *
 * 		[0] => c:\sites\marknotes\docs\a_note.md
 * 		[1] => c:\sites\marknotes\docs\private\a_note.md
 * 		[2] => c:\sites\marknotes\docs\public\a_note.md
 *
 * The returned value will be the same array but without
 * protected / not accessible Files so, f.i., if "private" is
 * protected and not accessible :
 *
 * 		[0] => c:\sites\marknotes\docs\a_note.md
 * 		[1] => c:\sites\marknotes\docs\public\a_note.md
 *
 * Can answer to /index.php?task=task.acls.filter_list
 * (but there is no output since this is an intern task)
 */
namespace MarkNotes\Plugins\Task\ACLs;

defined('_MARKNOTES') or die('No direct access allowed');

class FilterList extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.acls';
	protected static $json_options = JSON_OPTIONS_ACLS;

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		$aeDebug = \MarkNotes\Debug::getInstance();
		/*<!-- endbuild -->*/

		/** The list of protected folders, from settings.json
		 * For instance :
		 *
		 *		"private" : [ "Christophe", "Marc" ],
		 *		"marknotes/only_me" : [ "Christophe" ]
		 *
		 * i.e. a folder name and a list of allowed users.
		 */
		$arrOptions = self::getOptions('folders', array());

		if (count($arrOptions) > 0) {
			// Retrieve the user defined at the .htpasswd level
			// i.e. the user used to connect on the site when a
			// .htpasswd is used to protect the site
			$username = trim($_SERVER['PHP_AUTH_USER'] ?? '');
			if ($username == '') {
				$username = trim($_SERVER['REMOTE_USER'] ?? '');
			}

			foreach ($arrOptions as $folder => $arrUsers) {

				// Retrieve the fullname of the protected folder
				// For instance : c:\sites\marknotes\docs\private\
				$folder = $aeSettings->getFolderDocs().$folder;

				$i=0;

				/**
				 * $params is an array with filenames like :
				 * 	[0] => c:\sites\marknotes\docs\a_note.md
				 * 	[1] => c:\sites\marknotes\docs\private\a_note.md
				 * 	[2] => c:\sites\marknotes\docs\public\a_note.md
				 */
				foreach ($params as $entry) {
					// $entry should be an absolute filename
					// because $folder is also absolute
					$entry = $aeFiles->makeFileNameAbsolute($entry);

					// Does the filename ($entry) starts with the
					// protected foldername ?
					if (substr($entry, 0, strlen($folder)) === $folder) {
						// Yes ==> we've found a protected file
						// For instance
						// c:\sites\marknotes\docs\private\secret.md
						// Check if the user can see it

						// Check the username. If empty, no user is
						// connected so the file is not accessible
						// for him (visitor or bot)
						if ($username == '') {
							$params[$i] = '*protected*';
							/*<!-- build:debug -->*/
							$aeDebug->log("	The folder ".$folder." is protected and requires a valid user", "debug");
							/*<!-- endbuild -->*/
						} else {
							if (in_array($username, $arrUsers) !== true) {
								$params[$i] = '*protected*';
								/*<!-- build:debug -->*/
								$aeDebug->log("	The folder ".$folder." is protected; user ".$username." can't see it", "debug");
								/*<!-- endbuild -->*/
							/*<!-- build:debug -->*/
							} else {
								$aeDebug->log("	The folder ".$folder." is protected and user ".$username." is allowed to see it", "debug");
							/*<!-- endbuild -->*/
							}
						}
					} // if (substr($entry, 0, strlen($folder))

					$i+=1;
				} // foreach ($params as $entry)
			} // foreach ($arrOptions as $folder => $arrUsers)

			/**
			 * At this stage, protected files are mentionned
			 * in the array like :
			 *
			 *	 [3] => *protected*	(<-- the filename has been
			 *					 removed)
			 *
			 * So remove all *protected* entries
			 */

			$i=0;
			foreach ($params as $entry) {
				if ($entry == '*protected*') {
					unset($params[$i]);
				}

				$i+=1;
			}
		} // if (count($arrOptions)>0)

		return true;
	}

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		// Check if the ACLs task is well enabled
		$bCanRun = self::isEnabled(false);

		if ($bCanRun) {
			// Check if the plugin can be fired depending on
			// the running task (f.i. task.export.reveal)
			$bCanRun = parent::canRun();

			if ($bCanRun) {
				// This plugin is only needed when at least one folder
				// has been protected
				$arrOptions = self::getOptions('folders', array());
				$bCanRun = (count($arrOptions) > 0);
				if (!$bCanRun) {
					$aeSession = \MarkNotes\Session::getInstance();
					$aeSession->set('acls', null);
				}
			}
		}

		return $bCanRun;
	}
}
