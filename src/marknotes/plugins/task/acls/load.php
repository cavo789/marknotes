<?php
/**
 * This plugin will allow to restrict the access to a folder and make
 * this folder only visible to allowed people. So, for instance, in
 * the folder /docs/private is protected, that folder won't be visible
 * to users except if they can. If not, users won't see that folder
 * at all.
 *
 * In settings.json, just add this section :
 *
 * "plugins": {
 *		"options": {
 *			"task": {
 *				"acls" : {
 *					"folders" : {
 *						"private" : [ "Cédric", "Christophe", "Simon" ]
 *					}
 *				}
 *			}
 *		}
 *	}
 *
 * This means that the folder /private is protected and can only be
 * visible by Apache users mentionned in the list. "Apache users" :
 * users defined in a .htpasswd file placed at the root of the marknotes site
 *
 * *** BE CAREFULL *** : ENTRIES ARE CASE SENSITIVE. If you've an user
 * "christophe" defined in the .htpassw, don't use "Christophe" in your
 * settings.json. You need to respect the case !
 *
 * Note : if no folder is mentionned or if the /plugins/options/acls
 * entry is missing, this plugin has no effect.
 *
 * Can answer to /index.php?task=task.acls.load (but there is no output
 * since this is an intern task who initialize a session variable)
 */

namespace MarkNotes\Plugins\Task\ACLs;

defined('_MARKNOTES') or die('No direct access allowed');

class Load extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.acls';
	protected static $json_options = JSON_OPTIONS_ACLS;

	public static function run(&$params = null) : bool
	{
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the list of protected folders
		$arrOptions = self::getOptions('folders', array());

		if ($arrOptions == array()) {
			$aeSession->set('acls', null);
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("The ACLs plugin isn't configured so everything is accessible", "debug");
			}
			/*<!-- endbuild -->*/
		} else {
			// Be sure to have the correct directory separator
			// for folders i.e. / or \ depending on the Operating System
			foreach ($arrOptions as $folder => $value) {
				if (strpos($folder, '/') > 0) {
					$new = str_replace('/', DS, $folder);
					$arrOptions[$new] = $arrOptions[$folder];
					unset($arrOptions[$folder]);
				}
			}

			// Remember the options for the ACLs plugin
			$aeSession->set('acls', $arrOptions);
		}
		return true;
	}

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
}
