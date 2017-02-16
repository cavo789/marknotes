<?php

namespace AeSecureMDTasks;

/**
 * Get the list of .md files.  This list will be used in the "table of contents"
 */

class ListFiles
{
    
    public static function Run()
    {
  
        $aeSettings=\AeSecure\Settings::getInstance();
        
        $i=0;
        
        $arrFiles=\AeSecure\Functions::array_iunique(\AeSecure\Files::rglob('*.md', $aeSettings->getFolderDocs(true)));
    
        // Be carefull, folders / filenames perhaps contains accentuated characters
        $arrFiles=array_map('utf8_encode', $arrFiles);
      
        // Sort, case insensitve
        natcasesort($arrFiles);
     
        $return['settings']['root']=$aeSettings->getFolderDocs(true);
      
        // Get the number of files
        $return['count']=count($arrFiles);
      
        // --------------------------------------------------------------------------------------
        // Populate the tree that will be used for jsTree (see https://www.jstree.com/docs/json/)
      
        $folder=str_replace('/', DS, $aeSettings->getFolderDocs(true));
      
        // $arr is an array with arrays that contains arrays ...
        // i.e. the root folder that contains subfolders and subfolders can contains subfolders...
        $arrTreeFoldersAutoOpen=$aeSettings->getTreeFoldersAutoOpen();
        $arr=self::dir_to_jstree_array($folder, 'a', array('md'), $arrTreeFoldersAutoOpen);
      
        // Now, there are subfolders (arrays) where there was no .md files : don't display that folders
        // on the jsTree DOM elemen.  The "jstree_hide_emptyFolder" will add a "hidden" state for these
        // empty folders
        self::jstree_hide_emptyFolder($arr);
      
        // The array is now ready
        $return['tree']=$arr;

        header('Content-Type: application/json');
        echo json_encode($return, JSON_PRETTY_PRINT);      

    } // function Run()
    
   /**
    * Called by ListFiles().  Add a "hidden" state for arrays in the array (like folder and subfolders) when
    * the number of children is zero (i.e. that subfolder doesn't contains any relevant files)
    *
    * @param int $array
    * @param type $unwanted_key
    */
    private static function jstree_hide_emptyFolder(&$array)
    {
        if (isset($array['children'])) {
            if (count($array['children'])==0) {
                $array["state"]["hidden"]=1;
            }
        }
        foreach ($array as &$value) {
            if (is_array($value)) {
                self::jstree_hide_emptyFolder($value, 'children');
            }
        }
    } // function jstree_hide_emptyFolder()

   /**
    * Called by ListFiles().  Populate an array with the list of .md files.
    *
    * The structure of the array match the needed definition of the jsTree jQuery plugin
    *
    * http://stackoverflow.com/a/23679146/1065340
    *
    * @param type $dir     Root folder to scan
    * @param type $order   "a" for ascending
    * @param string $ext   array() with extensions to search for (only .md for this program)
    * @return array
    */
    private static function dir_to_jstree_array(string $dir, string $order = "a", array $ext = array(),
       array $arrTreeFoldersAutoOpen) : array
    {

        $aeSettings=\AeSecure\Settings::getInstance();
        $root=str_replace('/', DS, $aeSettings->getFolderDocs(true));

        if (empty($ext)) {
            $ext = array ("md");
        }
      
        // The first note, root node, will always be opened (first level)
        // As from the second level, pay attention to the _settingsTreeOpened flag.  If not set, nodes will be
        // set in a closed state (user will need to click on the node to see items)
        $opened=($root==$dir?1:$aeSettings->getTreeOpened());
      
        // if $this->_settingsFoldersAutoOpen if not empty, check if that folder is currently processing
   
        if ($opened==false) {
            if (in_array(rtrim(utf8_encode($dir), DS), $arrTreeFoldersAutoOpen)) {
                $opened=true;
            }
        }
        // Entry for the folder
        $listDir = array(
         'id' => utf8_encode(str_replace($root, '', $dir).DS),
         'type'=>'folder',
         'icon'=>'folder',
         'text' =>basename(utf8_encode($dir)),
         'state'=>array('opened'=>$opened,'disabled'=>1),
         'children' => array());
      
        $files = array();
        $dirs = array();

        if ($handler = opendir($dir)) {
            while (($sub = readdir($handler)) !== false) {
                if ($sub != "." && $sub != "..") {
                    if (is_file($dir.DS.$sub)) {
                        $extension = pathinfo($dir.DS.$sub, PATHINFO_EXTENSION);
                        if (in_array($extension, $ext)) {
                            $files[]=array(
                              'icon'=>'file file-md',
                              'text'=>str_replace('.'.$extension, '', utf8_encode($sub)), // Don't display the extension
                        
                              // Populate the data attribute with the task to fire and the filename of the note
                              'data'=>array(
                                 'task'=>'display',
                                 'file'=>utf8_encode(str_replace($root, '', $dir.DS.$sub))
                               )
                            );
                        }
                    } elseif (is_dir($dir.DS.$sub)) {
                        $dirs []= rtrim($dir, DS).DS.$sub;
                    }
                }
            } // while
         
            if ($order === "a") {
                asort($dirs);
            } else {
                arsort($dirs);
            }

            foreach ($dirs as $d) {
                $listDir['children'][]=self::dir_to_jstree_array($d, $order, $ext, $arrTreeFoldersAutoOpen);
            }

            if ($order === "a") {
                asort($files);
            } else {
                arsort($files);
            }

            foreach ($files as $file) {
                $listDir['children'][]= $file;
            }

            closedir($handler);
        } // if($handler = opendir($dir))
      
        return $listDir;
        
    } // function dir_to_jstree_array()

} // class ListFiles