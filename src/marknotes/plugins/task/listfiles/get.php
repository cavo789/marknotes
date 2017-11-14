<?php
/**
 * Get the list of .md files present in the /docs folder.
 * This plugin will make sure, thanks to ACLs plugin, that
 * files are accessible to the visitor
 *
 * This task won't return a visible output (no json, no html, ...)
 * but will initialize an array in his run() function.
 *
 * Example of a call :
 *
 *		$arrFiles = array();

 *		// Call the listfiles.get event and initialize $arrFiles
 *		$aeEvents = \MarkNotes\Events::getInstance();
 *		$args=array(&$arrFiles);
 *		$aeEvents->loadPlugins('task.listfiles.get');
 *		$aeEvents->trigger('task.listfiles.get::run', $args);
 *
 *		$arrFiles = $args[0];
 *
 *		foreach ($arrFiles as $file) {
 *			echo "Dear visitor, the file " . $file . " is accessible to ".
 *				"you</br>";
 *		}
 *
 * Can answer to /index.php?task=task.listfiles.get
 * (but there is no output)
 */
namespace MarkNotes\Plugins\Task\ListFiles;

defined('_MARKNOTES') or die('No direct access allowed');

class Get extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.listfiles';
	protected static $json_options = 'plugins.options.task.listfiles';

	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$arrSettings = $aeSettings->getPlugins('/interface');
		$show_tree_allowed = boolval($arrSettings['show_tree_allowed'] ?? 1);

		if (!$show_tree_allowed) {
			// The webmaster has disabled to right to see
			// the interface so, it seems coherent to also
			// disable the listfiles task.
			$arrFiles = array();
		} else {
			// Call the ACLs plugin
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->loadPlugins('task.acls.load');
			$args=array();
			$aeEvents->trigger('task.acls.load::run', $args);

			// $bACLsLoaded will be set true if at least one folder is
			// protected
			$bACLsLoaded = boolval($aeSession->get('acls', '') != '');

			$arrFiles = array();
			// Due to the ACLs plugin, the list of folders that are returned
			// by this script can vary from one user to an another so we
			// can't store the information at the session level (or to a
			// "user" level)
			if (!$bACLsLoaded) {
				$arrOptimize = $aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);

				$bOptimize = $arrOptimize['server_session'] ?? false;
				if ($bOptimize) {
					// Get the list of files/folders from the session
					// object if possible
					$arrFiles = json_decode(trim($aeSession->get('ListFiles', '')), true);

					if (count($arrFiles)==0) {
						$arrFiles = array();
					}
				}
			} // if (!$bACLsLoaded)

			if ($arrFiles === array()) {
				// The array is empty
				$aeFiles = \MarkNotes\Files::getInstance();

				$docs = $aeSettings->getFolderDocs(true);

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug->log('Get list of files in ['.$docs.']', 'debug');
				}
				/*<!-- endbuild -->*/

				$ext = '*.{markdown,md}';
				$arrFiles = $aeFunctions->array_iunique($aeFiles->rglob($ext, $docs));

				if ($bACLsLoaded) {
					// Run the filter_list task to remove any protected files
					// not allowed for the current user

					$aeEvents->loadPlugins('task.acls.filter_list');
					$args=array(&$arrFiles);
					$aeEvents->trigger('task.acls.filter_list::run', $args);

					// Retrieve the filtered array i.e. that Files
					// well accessible to the current user
					$arrFiles=$args[0];
				} // if ($bACLsLoaded)

				if (!$bACLsLoaded) {
					if ($bOptimize) {
						// Get the list of files/folders from the session object if possible
						$aeSession->set('ListFiles', json_encode($arrFiles));
					}
				}
			} // if ($arrFiles === array())
		} // if (!$show_tree_allowed)

		// Return the array with files accessible to the current user
		$params = $arrFiles;

		return true;
	}
}
