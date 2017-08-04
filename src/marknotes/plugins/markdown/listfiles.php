<?php

/**
 * ListFiles : retrieve the list of files under a specific folder
 * This plugin will replace the tag '%LISTFILES myFolder' by a bullet list with any files
 * under the myFolder (and subfolder) folder.
 *
 * Just like if the user has manually introduce this bullet list
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class ListFiles
{

    /**
     * The markdown file has been read, this function will get the content of the .md file and
     * make some processing like data cleansing
     *
     * $params is a associative array with, as entries,
     *	* markdown : the markdown string (content of the file)
     *	* filename : the absolute filename on disk
    */
    public static function readMD(&$params = null)
    {
        if (trim($params['markdown']) === '') {
            return true;
        }

        // Check the presence of the LISTFILES tag
        if (preg_match_all('/%LISTFILES ([^\\%]*)%/', $params['markdown'], $matches)) {
            $aeFiles = \MarkNotes\Files::getInstance();
            $aeFunctions = \MarkNotes\Functions::getInstance();
            $aeSettings = \MarkNotes\Settings::getInstance();
            $aeSession = \MarkNotes\Session::getInstance();

            // Retrieve the note fullpath
            $root = str_replace('/', DS, dirname($params['filename'])).DS;

            $arrTags = $matches[0];
            $arrFolders = $matches[1];

            $i = 0;
            $j = count($arrFolders);

            for ($i == 0; $i < $j; $i++) {

                // Retrieve the full folder name like
                // C:\sites\notes\docs\folder\subfolder
                $folder = str_replace('/', DS, $arrFolders[$i]);

                if (!(is_dir($folder))) {
                    $folder = $root.$folder.DS;
                }

                if (!(is_dir($folder))) {
                    // Correctly handle accentuated characters
                    $folder = utf8_decode($folder);
                }

                if (is_dir($folder)) {

                    // Retrieve the list of files under that $folder
                    $arrFiles = $aeFiles->rglob('*', $folder);

                    // Do we need to encode accent on that system ?
                    $bEncodeAccents = boolval($aeSettings->getFiles('encode_accent', 0));

                    $sList = '';
                    foreach ($arrFiles as $file) {
                        if (is_file($file)) {

                            // Don't take files starting with a dot
                            if (substr(basename($file), 0, 1) !== '.') {
                                if ($bEncodeAccents) {
                                    $file = utf8_encode($file);
                                }

                                $relURL = str_replace($aeSettings->getFolderWebRoot(), '', $file);
                                $relURL = str_replace(DS, '/', $relURL);
                                $file = str_replace(utf8_decode($root), '', str_replace($root, '', $file));

                                $sList .= "*   [".$file."](".str_replace(' ', '%20', $relURL).")".PHP_EOL;
                            }
                        } // if (is_file($file))
                    } // foreach ($arrFiles as $file)

                    $params['markdown'] = str_replace($arrTags[$i], $sList, $params['markdown']);
                } // if (is_dir($folder))
            } // for
        } // if (preg_match_all('/%LISTFILES

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('markdown.read', __CLASS__.'::readMD');
        return true;
    }
}
