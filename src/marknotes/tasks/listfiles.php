<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

/**
 * Get the list of .md files.  This list will be used in the "table of contents"
 */

class ListFiles
{
    protected static $hInstance = null;

    public function __construct()
    {
        return true;
    } // function __construct()

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new ListFiles();
        }

        return self::$hInstance;
    } // function getInstance()

    public static function run()
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $sReturn = '';

        if ($aeSettings->getOptimisationUseServerSession()) {
            // Get the list of files/folders from the session object if possible
            $aeSession = \MarkNotes\Session::getInstance();
            $sReturn = $aeSession->get('ListFiles', '');
        }

        if ($sReturn === '') {

            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                $aeDebug->log('Get list of files in ['.$aeSettings->getFolderDocs(true).']', 'debug');
            }
            /*<!-- endbuild -->*/

            $arrFiles = $aeFunctions->array_iunique($aeFiles->rglob('*.md', $aeSettings->getFolderDocs(true)));

            // Be carefull, folders / filenames perhaps contains accentuated characters
            $arrFiles = array_map('utf8_encode', $arrFiles);

            $return['settings']['root'] = $aeSettings->getFolderDocs(true);

            // Get the number of files
            $return['count'] = count($arrFiles);

            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                $aeDebug->log($return['count'].' files found', 'debug');
            }
            /*<!-- endbuild -->*/

            // --------------------------------------------------------------------------------------
            // Populate the tree that will be used for jsTree (see https://www.jstree.com/docs/json/)

            $folder = str_replace('/', DS, $aeSettings->getFolderDocs(true));

            // $arr is an array with arrays that contains arrays ...
            // i.e. the root folder that contains subfolders and subfolders can contains subfolders...
            $arr = self::dir_to_jstree_array($folder, array('md'), $aeSettings->getTreeAutoOpen());

            // The array is now ready
            $return['tree'] = $arr;

            $aeJSON = \MarkNotes\JSON::getInstance();
            $sReturn = $aeJSON->json_encode($return);

            if ($aeSettings->getOptimisationUseServerSession()) {
                // Remember for the next call
                $aeSession->set('ListFiles', $sReturn);
            }
        } // if (count($arr)>0)

        return $sReturn;
    }

    /**
    * Called by ListFiles().  Populate an array with the list of .md files.
    *
    * The structure of the array match the needed definition of the jsTree jQuery plugin
    *
    * http://stackoverflow.com/a/23679146/1065340
    *
    * @param  type   $dir   Root folder to scan
    * @param  type   $order "a" for ascending
    * @param  string $ext   array() with extensions to search for (only .md for this program)
    * @return array
    */
    private static function dir_to_jstree_array(
        string $dir,
        array $ext = array(),
        array $arrTreeAutoOpen
    ) : array {
        $aeSettings = \MarkNotes\Settings::getInstance();
        $root = str_replace('/', DS, $aeSettings->getFolderDocs(true));
        $rootNode = $aeSettings->getFolderDocs(false);

        if (empty($ext)) {
            $ext = array("md");
        }

        // Is there a default node to select after the load of the treeview ?
        $defaultNode = $aeSettings->getTreeviewDefaultNode('');
        if ($defaultNode !== '') {
            // Should be an absolute filename like C:\notes\docs\marknotes\readme.md
            $defaultNode = $aeSettings->getFolderDocs(true).trim($defaultNode);

            if (substr($defaultNode, -3) !== '.md') {

                // And the extension should also be mentionned
                $defaultNode .= '.md';
            }
        }

        // The first note, root node, will always be opened (first level)
        // As from the second level, pay attention to the _settingsTreeOpened flag.  If not set, nodes will be
        // set in a closed state (user will need to click on the node to see items)
        $opened = ($root == $dir?1:$aeSettings->getTreeOpened());

        // if $this->_settingsFoldersAutoOpen if not empty, check if that folder is currently processing

        if ($opened == false) {
            if (in_array(rtrim(utf8_encode($dir), DS), $arrTreeAutoOpen)) {
                $opened = true;
            }
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Use utf8_encode only under Windows OS
            $sDirectoryText = utf8_encode(basename($dir));
        } else {
            $sDirectoryText = basename($dir);
        }

        // Entry for the folder
        $listDir = array(
            'id' => utf8_encode(str_replace($root, '', $dir).DS),
            'type' => 'folder',
            'icon' => 'folder',
            'text' => $sDirectoryText,
            'state' => array('opened' => $opened,'disabled' => 1),
            'data' => array(
                'url' => utf8_encode(str_replace(DS, '/', str_replace($root, '', $dir)))
            ),
            'children' => array()
        );

        $files = array();
        $dirs = array();

        if ($handler = opendir($dir)) {
            while (($sub = readdir($handler)) !== false) {
                if ($sub != "." && $sub != "..") {
                    // Don't take files/folders starting with a dot
                    if (substr($sub, 0, 1) !== '.') {
                        if (is_file($dir.DS.$sub)) {
                            $opened = 0;
                            if ($dir.DS.$sub === $defaultNode) {
                                $opened = 1;
                            }
                            $extension = pathinfo($dir.DS.$sub, PATHINFO_EXTENSION);
                            if (in_array($extension, $ext)) {

                                // Filename but without the extension (and no path)
                                $filename = str_replace('.'.$extension, '', $sub);

                                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                    // Use utf8_encode only under Windows OS
                                    $sFileText = utf8_encode($filename);
                                } else {
                                    $sFileText = $filename;
                                }

                                $files[] = array(
                                    'id' => md5(str_replace($root, $rootNode, $dir.DS.$sub)),
                                    'icon' => 'file file-md',
                                    'text' => $sFileText,

                                    // Populate the data attribute with the task to fire and the filename of the note
                                    'data' => array(
                                        'task' => 'display',
                                        'file' => utf8_encode(str_replace($root, '', $dir.DS.$sub)),
                                        'url' => utf8_encode(str_replace(DS, '/', str_replace($root, '', $dir.DS.$filename)))
                                    ),
                                    'state' => array(
                                        'opened' => $opened,
                                        'selected' => $opened
                                    )
                                );
                            }
                        } elseif (is_dir($dir.DS.$sub)) {
                            $dirs [] = rtrim($dir, DS).DS.$sub;
                        }
                    } // if (substr($sub, 0, 1)!=='.')
                }
            } // while

            foreach ($dirs as $d) {
                $listDir['children'][] = self::dir_to_jstree_array($d, $ext, $arrTreeAutoOpen);
            }

            foreach ($files as $file) {
                $listDir['children'][] = $file;
            }

            closedir($handler);
        } // if($handler = opendir($dir))

        return $listDir;
    }
}
