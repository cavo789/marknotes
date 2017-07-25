<?php

/**
 * This plugin will allow to restrict the access to a folder and make this folder only
 * visible to allowed people. So, for instance, in the folder /docs/private is protected,
 * that folder won't be visible to users except if they can. If not, users won't see that folder
 * at all.
 *
 * In settings.json, just add this section :
 *
 * "plugins": {
 *		"options": {
 *			"acls" : {
 *				"folders" : {
 *					"private" : [
 *						"Cédric",
 *						"Christophe",
 *						"Simon",
 *						"Xavier"
 *					]
 *				}
 *		}
 *	}
 *
 * This means that the folder /private is protected and can only be visible by
 * Apache users mentionned in the list. "Apache users" : users defined in a .htpasswd file
 * placed at the root of the marknotes site
 *
 * *** BE CAREFULL *** : ENTRIES ARE CASE SENSITIVE. If you've an user "christophe" defined
 * in the .htpassw, don't use "Christophe" in your settings.json. You need to respect the case !
 *
 * Note : if no folder is mentionned or if the /plugins/options/acls entry is missing, this
 * plugin has no effect.
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class ACLs
{
    public static function run(&$params = null) : bool
    {
        $bReturn = true;

        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        // Retrieve the options of the ACLs plugin
        $arrSettings = $aeSession->get('acls');

        if (isset($arrSettings['folders'])) {
            $folders = $arrSettings['folders'];

            // $folder will be f.i. "private" (this is the name of a folder) and
            // $arrUsers will be f.i. [ "Cédric", "Christophe", "Simon", "Xavier" ]

            foreach ($folders as $folder => $arrUsers) {
                $folder = trim($folder);

                // Don't process if no folder name has been mentionned
                // But well if no users are mentionned : if $arrUsers is empty, consider that
                // no one can see the folder.
                if ($folder === "") {
                    continue;
                }

                // Be sure that the directory separator is well mentionned at the left
                // and at the right of the folder name
                $folder = rtrim(DS.ltrim($folder, DS), DS).DS;

                // The check below is :
                //
                // $folder contains the folder to protect (f.i. /private)
                // $params['folder'] is the folder retrieved by the glob() action ("one folder")
                //
                // So : if $params['folder']==$folder ==> that folder is protected.
                // But also every folder starting with that root name
                // (/private/billing, /private/invoices, /private/home, ...).
                //
                // Therefore the use of substr()

                if (substr($params['folder'], 0, strlen($folder)) === $folder) {

                    // Retrieve the user defined at the .htpasswd level
                    // i.e. the user used to connect on the site when a .htpasswd is used
                    // to protect the site
                    $username = $_SERVER['PHP_AUTH_USER'] ?? '';

                    // Be carefull, names are case sensitive. If the connected user is
                    // "christophe", the $arrUsers array should mentionned "christophe" (and
                    // not "Christophe") in order that the check can be OK.

                    if (in_array($username, $arrUsers) !== true) {

                        // No, the $username isn't in the array of allowed user => this
                        // user can't see the folder

                        $bReturn = false;

                        if ($aeSettings->getDevMode()) {
                            $aeDebug->log('   The folder '.$params['folder'].' is protected and only valid user(s) can see it. The current user ['.$username.'] isn\'t in the list of allowed people (defined in settings.json) so access is denied for him', 'info');
                        }
                    } else {

                        // Yes ! the user is a valid one, he can see the folder.

                        $bReturn = true;
                    } // if (in_array($username, $arrUsers) !== true)
                }
            }

            $params['return'] = $bReturn;
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $arrSettings = $aeSettings->getPlugins('options', 'acls');

        if (count($arrSettings) == 0) {
            return false;
        }

        // Be sure to have the correct directory separator for folders i.e. / or \ depending
        // on the Operating System

        if (isset($arrSettings['folders'])) {
            $arrFolders = $arrSettings['folders'];
            foreach ($arrFolders as $folder => $value) {
                if (strpos($folder, '/') > 0) {
                    $new = str_replace('/', DS, $folder);
                    $arrSettings['folders'][$new] = $arrFolders[$folder];
                    unset($arrSettings['folders'][$folder]);
                }
            }
        }

        // Remember the options for the ACLs plugin
        $aeSession->set('acls', $arrSettings);

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('canSeeFolder', __CLASS__.'::run');

        return true;
    }
}
