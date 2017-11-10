<?php
/**
 * This plugin implement the task.acls.cansee event i.e. "Can this folder
 * be accessible to the visitor ?"
 *
 * Example of a call :
 *
 *		$aeEvents = \MarkNotes\Events::getInstance();
 *		$aeEvents->loadPlugins('task.acls.cansee');
 *
 *		// Note : the folder should start and end with the slash
 *		$arr = array(
 *			 'folder' => '/private_folder/',
 *			 'return' => true);
 *		$args = array(&$arr);
 *
 *		$aeEvents->trigger('task.acls.cansee::run', $args);
 *
 *		echo 'The folder '.$args[0]['folder'].' is '.
 *			($args[0]['folder']?'accessible':'prohibited');
 *
 * Can answer to /index.php?task=task.acls.cansee (but there is no output since this
 * is an intern task who initialize a boolean variable)
 */
namespace MarkNotes\Plugins\Task\ACLs;

defined('_MARKNOTES') or die('No direct access allowed');

class canSee extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.acls';
	protected static $json_options = JSON_OPTIONS_ACLS;

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
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

		return $bCanRun;
	}

	public static function run(&$params = null) : bool
	{
		$bReturn = true;

		if (!isset($params['folder'])) {
			/*<!-- build:debug -->*/
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('   The task task.acls.cansee should be called with a '.
				'foldername (like "private"); that foldername should be put in the '.
				'$params[\'folder\'] parameter. It\'s not the case here.', 'error');
			/*<!-- endbuild -->*/
			return false;
		}

		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$checkFolder = $params['folder'];

		// The name of the folder to check must be absolute so
		// add the full path if needed
		if (strpos($checkFolder, $aeSettings->getFolderDocs(true)) === false) {
			$checkFolder = $aeSettings->getFolderDocs(true).$checkFolder;
		}

		$checkFolder = rtrim($checkFolder,DS).DS;

		$arrOptions = self::getOptions('folders', array());

		if (count($arrOptions) > 0) {
			// Retrieve the user defined at the .htpasswd level
			// i.e. the user used to connect on the site when a .htpasswd is used
			// to protect the site
			$username = trim($_SERVER['PHP_AUTH_USER'] ?? '');
			if ($username == '') {
				$username = trim($_SERVER['REMOTE_USER'] ?? '');
			}

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("   Can see ".$params['folder']." ?", "debug");
			}
			/*<!-- endbuild -->*/

			foreach ($arrOptions as $folder => $arrUsers) {
				// Retrieve the fullname of the protected folder
				// For instance : c:\sites\marknotes\docs\private\
				$folder = $aeSettings->getFolderDocs().$folder.DS;

				// Both $folder and $checkFolder are absolute paths and
				// have the final slash
				if (substr($checkFolder, 0, strlen($folder)) === $folder) {
					// Yes ==> we've found a protected file
					// Yes ==> we've found a protected file
					// For instance c:\sites\marknotes\docs\private\secret.md
					// Check if the user can see it
					// Check the username. If empty, no user is connected
					// so the file is not accessible for him (visitor or bot)
					if ($username == '') {
						$bReturn = false;
						/*<!-- build:debug -->*/
						$aeDebug->log("   The folder ".$folder." is protected and requires a valid user", "debug");
						/*<!-- endbuild -->*/
					} else {
						if (in_array($username, $arrUsers) !== true) {
							$bReturn = false;
							/*<!-- build:debug -->*/
							$aeDebug->log("   The folder ".$folder." is protected; user ".$username." can't see it", "debug");
							/*<!-- endbuild -->*/
						/*<!-- build:debug -->*/
						} else {
							$aeDebug->log("   The folder ".$folder." is protected and  user ".$username." is allowed to see it", "debug");
						/*<!-- endbuild -->*/
						}
					} // if ($username == '')
				} // if (substr($params['folder']
			} // foreach ($arrOptions
		} // if (count($arrOptions) > 0)

		$params['return'] = ($bReturn ? 1 : 0);

		return true;
	}
}
