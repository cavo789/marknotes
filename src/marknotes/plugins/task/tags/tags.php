<?php

/**
 * What are the actions to fired when MarkNotes is running the "pdf" task ?
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class Tags
{
    private static function getFolders() : array
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // Get the list of tags if not yet found in the Session object
        $dirs = array_filter(glob($aeSettings->getFolderDocs(true).'*'), 'is_dir');
        natcasesort($dirs);

        $iter = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($aeSettings->getFolderDocs(true), \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );

        $paths = array();
        foreach ($iter as $path => $dir) {
            if ($dir->isDir()) {
                $paths[] = basename($path);
            }
        }

        $paths = $aeFunctions->array_iunique($paths, SORT_STRING);

        $tmp = '';
        foreach ($paths as $dir) {
            $tmp .= basename($dir).';';
        }
        $tmp = rtrim($tmp, ';');

        $arrFolders = explode(';', $tmp);

        return $arrFolders;
    }

    public static function run(&$params = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        // get the list of folders and generate a "tags" node
        $sReturn = '';

		$arrOptimize = $aeSettings->getPlugins('options','optimize');
		$bOptimize = $arrOptimize['server_session'] ?? false;
		if ($bOptimize) {
            // Get the list of files/folders from the session object if possible
            // If found, it's a JSON object
            $sReturn = $aeSession->get('Tags', '');
        }

        $sReturn = '';

        if ($sReturn == '') {

            // Get the list of folders
            $arr = self::getFolders();

            // And append tags
            $arrTags = array();

            if ($aeFiles->fileExists($fname = $aeSettings->getFolderWebRoot().'tags.json')) {
                $aeJSON = \MarkNotes\JSON::getInstance();
                if (filesize($fname) > 0) {

                    $arrTags = $aeJSON->json_decode($fname, true);

					if (count($arrTags)>0) {
	                    foreach ($arrTags as $tag) {
	                        $arr[] = $tag;
	                    }
					}
                }
            }

            // natcasesort and array_iuniquemakes an associative array with positions, not needed
            $arrTags = $arr;
            $arr = $aeFunctions->array_iunique($arr, SORT_STRING);
            natcasesort($arr);
            $arrTags = array();
            foreach ($arr as $key => $value) {
                $arrTags[] = array('name' => $value);
            }

            // Be carefull, folders / filenames perhaps contains accentuated characters
            //$arrTags = array_map('utf8_encode', $arrTags);

            $aeJSON = \MarkNotes\JSON::getInstance();
            $sReturn = $aeJSON->json_encode($arrTags);

            if ($bOptimize) {
                // Remember for the next call
                $aeSession->set('Tags', $sReturn);
            }
        } // if (count($arrTags)==0)

        header('Content-Type: application/json; charset=UTF-8');
        header("cache-control: must-revalidate");
        $offset = 48 * 60 * 60;  // 48 hours
        $expire = "expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        header($expire);

        echo $sReturn;

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('run.task', __CLASS__.'::run');
        return true;
    }
}
