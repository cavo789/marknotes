<?php

namespace AeSecure\Tasks;

/**
* Retrieve the list of folder names and tags.  Used for the search entry, allowing auto-completion
*
* @return array
*/

class Tags
{
    public static function run()
    {

        $aeSettings=\AeSecure\Settings::getInstance();

        // get the list of folders and generate a "tags" node

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

        $paths=\AeSecure\Functions::array_iunique($paths, SORT_STRING);

        natcasesort($paths);

        $tmp='';
        foreach ($paths as $dir) {
            $tmp.=utf8_encode(basename($dir)).';';
        }
        $tmp=rtrim($tmp, ';');

        $return=array();

        if (\AeSecure\Files::fileExists($fname = $aeSettings->getFolderWebRoot().'tags.json')) {
            if (filesize($fname)>0) {
                $aeJSON=\AeSecure\JSON::getInstance();

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

        header('Content-Type: application/json');

        echo json_encode($return, JSON_PRETTY_PRINT);

        die();
    } // function Run()
} // class Tags
