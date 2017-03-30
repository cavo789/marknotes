<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

/**
* Retrieve the list of folder names and tags.  Used for the search entry, allowing auto-completion
*
* @return array
*/

class Tags
{

    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new Tags();
        }

        return self::$_instance;
    }

    public static function run()
    {

        $aeSettings=\MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        // get the list of folders and generate a "tags" node
        $sReturn = '';

        if ($aeSettings->getOptimisationUseServerSession()) {
            // Get the list of files/folders from the session object if possible
            // If found, it's a JSON object
            $sReturn=$aeSession->get('Tags', '');
        }

        if ($sReturn=='') {
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

            $aeFiles=\MarkNotes\Files::getInstance();
            $aeFunctions=\MarkNotes\Functions::getInstance();

            $paths=$aeFunctions->array_iunique($paths, SORT_STRING);

            natcasesort($paths);

            // Be carefull, folders / filenames perhaps contains accentuated characters
            $paths=array_map('utf8_encode', $paths);


            $tmp='';
            foreach ($paths as $dir) {
                $tmp.=basename($dir).';';
            }
            $tmp=rtrim($tmp, ';');

            $return=array();
            $arrTags=array();

            if ($aeFiles->fileExists($fname = $aeSettings->getFolderWebRoot().'tags.json')) {
                $aeJSON=\MarkNotes\JSON::getInstance();
                if (filesize($fname)>0) {
                    $arrTags=$aeJSON->json_decode($fname, true);

                    foreach ($arrTags as $tag) {
                        $return[]=array('name'=>$tag,'type'=>'tag');
                    }
                }
            }

            $tmp=explode(';', $tmp);
            foreach ($tmp as $folder) {
                $return[]=array('name'=>$folder,'type'=>'folder');
            }

            $sReturn = json_encode($return, JSON_PRETTY_PRINT);

            if ($aeSettings->getOptimisationUseServerSession()) {
                // Remember for the next call
                $aeSession->set('Tags', $sReturn);
            }
        } // if (count($arrTags)==0)

        return $sReturn;
    } // function Run()
} // class Tags
