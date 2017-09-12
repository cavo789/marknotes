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

        // Due to the ACLs plugin, the list of folders that are returned by
        // this script can vary from one user to an another so we can't store
        // the information at the session level (or to a "user" level)
        //$arrOptimize = $aeSettings->getPlugins('options','optimize');
		//$bOptimize = $arrOptimize['server_session'] ?? false;
		//if ($bOptimize) {
        //    // Get the list of files/folders from the session object if possible
        //    $aeSession = \MarkNotes\Session::getInstance();
        //    $sReturn = $aeSession->get('ListFiles', '');
        //}

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

            $aeDebug->enable(true);

            // --------------------------------------------------------------------------------------
            // Populate the tree that will be used for jsTree
            // (see https://www.jstree.com/docs/json/)
            $folder = str_replace('/', DS, $aeSettings->getFolderDocs(true));

            // $arr is an array with arrays that contains arrays ...
            // i.e. the root folder that contains subfolders and subfolders
            // can contains subfolders...
            $arr = self::dir_to_jstree_array($folder);

            // The array is now ready
            $return['tree'] = $arr;

            $aeJSON = \MarkNotes\JSON::getInstance();
            $sReturn = $aeJSON->json_encode($return);

            // Due to the ACLs plugin, the list of folders that are returned by
            // this script can vary from one user to an another so we can't store
            // the information at the session level (or to a "user" level)
            //if ($bOptimize()) {
            //    // Remember for the next call
            //    $aeSession->set('ListFiles', $sReturn);
            //}
        } // if (count($arr)>0)

        return $sReturn;
    }

    /**
     * Get an array that represents directory tree
     * @param string $directory     Directory path
     */
   public static function directoryToArray($directory, $recursive = false)
   {
       static $root = '';

       $aeFiles = \MarkNotes\Files::getInstance();

       if ($root === '') {
           $aeSettings = \MarkNotes\Settings::getInstance();
           $root = str_replace('/', DS, $aeSettings->getFolderDocs(true));
       }

       $arr = array();

       if (is_dir($directory)) {
           $handle = opendir($directory);
           if ($handle) {
               while (false !== ($file = readdir($handle))) {
                   // Don't take files/folders starting with a dot


                   if (substr($file, 0, 1) !== '.') {

                      // Absolute filename / foldername
                      $name = rtrim($directory, DS).DS.$file;

                       if (is_dir($name)) {

                              // It's a folder
                              if ($recursive) {
                                  $arr = array_merge(self::directoryToArray(rtrim($directory, DS).DS.$file, $recursive));
                              }

                           $arr[] = array('name' => $name,'type' => 'folder');
                       } else { // if (is_dir($directory.DS.$file))

                              // it's a file, get it only if the extension is .md
                              $extension = pathinfo($name, PATHINFO_EXTENSION);

                           if ($extension === 'md') {
                               $arr[] = array('name' => $name,'type' => 'file');
                           }
                       } // if (is_dir($directory.DS.$file))
                   } // if (substr($file, 0, 1) !== '.')
               } // while

               closedir($handle);
           } // if ($handle)
       } // if ($aeFiles->folderExists($directory))

       $name = array();

      // Sort the array by name
      foreach ($arr as $key => $row) {
          $name[$key] = $row['name'];
      } // foreach

      array_multisort($name, SORT_ASC | SORT_NATURAL | SORT_FLAG_CASE, $arr);

       return $arr;
   } // private function directoryToArray

    /**
    * Called by ListFiles().  Populate an array with the list of .md files.
    *
    * The structure of the array match the needed definition of the jsTree jQuery plugin
    * http://stackoverflow.com/a/23679146/1065340
    *
    * @param  type   $dir   Root folder to scan
    * @param  string $ext   array() with extensions to search for (only .md for this program)
    * @return array
    */
    private static function dir_to_jstree_array(string $dir) : array
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        /* ----------------------------------------------------------------------------
           TODO: Understand why on somes Windows computer (my home one), this function
           shouldn't use utf8_encode() for returning files/folders name but on some other (my
           office computer) yes, utf8_encode should be used. Strange!
           June 2017 : for the moment, I've created a files->encode_accent option in the
           settings.json file. By default, set to 0.
           ---------------------------------------------------------------------------- */
        $bEncodeAccents = boolval($aeSettings->getFiles('encode_accent', 0));

        $root = str_replace('/', DS, $aeSettings->getFolderDocs(true));
        $rootNode = $aeSettings->getFolderDocs(false);

        // Get the list of files and folders for the treeview
        $arrEntries = self::directoryToArray($dir, false);

        // Now, prepare the JSON return

        if ($bEncodeAccents) {
            // Use utf8_encode only under Windows OS
            $sDirectoryText = utf8_encode(basename($dir));
        } else {
            $sDirectoryText = basename($dir);
        }

        // Entry for the folder
        // ($root==$dir) ==> on the first visit on the page (very first visit),
        // the root directory will be opened so the treeview will display the list
        // of files and folders under the root.
        $listDir = array(
            'id' => utf8_encode(str_replace($root, '', $dir).DS),
            'type' => 'folder',
            'icon' => 'folder',
            'text' => $sDirectoryText,
            'state' => array('opened' => (($root == $dir)?1:0),'disabled' => 1),
            'data' => array(
                'url' => utf8_encode(str_replace(DS, '/', str_replace($root, '', $dir)))
            ),
            'children' => array()
        );

        $dirs = array();
        $files = array();

        foreach ($arrEntries as $entry) {
            if ($entry['type'] == 'file') {
                $opened = 0;
                // Filename but without the extension (and no path)
                $filename = str_replace('.md', '', basename($entry['name']));

                $id = str_replace($root, $rootNode, $entry['name']);
                $sFileText = $filename;

                if ($bEncodeAccents) {
                    // Use utf8_encode only under Windows OS
                    $sFileText = utf8_encode($filename);
                    $id = utf8_encode($id);
                }

                $files[] = array(
                    'id' => md5($id),
                    'icon' => 'file file-md',
                    'text' => $sFileText,
                    // Populate the data attribute with the task to fire
                    // and the filename of the note
                    'data' => array(
                        'task' => 'display',
                        'file' => utf8_encode(str_replace($root, '', $entry['name'])),
                        'url' => utf8_encode(str_replace(DS, '/', str_replace($root, '', str_replace('.md', '', $entry['name']))))
                    ),
                    'state' => array(
                        'opened' => $opened,
                        'selected' => $opened
                    )
                ); // $files[]
            } elseif ($entry['type'] == 'folder') {

                // Check if the folder can be displayed or not

                $aeEvents->loadPlugins('task', 'acls');
                $params = '';

                $fname = DS.ltrim(rtrim(str_replace($root, '', $entry['name']), DS), DS).DS;

                if ($bEncodeAccents) {
                    $fname = utf8_encode($fname);
                }

                // The folder should start and end with the slash
                $tmp = array(
                    'folder' => $fname,
                    'return' => true);

                $args = array(&$tmp);
                $aeEvents->trigger('canSeeFolder', $args);

                // The canSeeFolder event will initialize the 'return' parameter
                // to false when the current user can't see the folder i.e. don't
                // have the permission to see it. This permission is defined in the
                // acls plugin options
                //
                // See function run() of MarkNotes\Plugins\Task\ACLs for more
                // information

                if ($args[0]['return'] === true) {
                    $dirs [] = $entry['name'];
                }
            } // } elseif ($entry['type']=='folder')
        } // foreach

        if (count($dirs) > 0) {
            foreach ($dirs as $d) {
                $listDir['children'][] = self::dir_to_jstree_array($d);
            }
        }

        foreach ($files as $file) {
            $listDir['children'][] = $file;
        }

        return $listDir;
    }
}
