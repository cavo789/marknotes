<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-03-21T22:24:08.031Z
*/?>
<?php

namespace AeSecureMDTasks;

/**
* Kill a note or an entire directory
*/

class Delete
{
    public static function run(array $params)
    {

        header('Content-Type: text/html; charset=utf-8');

        if (!class_exists('Debug')) {
            include_once dirname(dirname(__FILE__)).'/debug.php';
        }

        $aeDebug=\AeSecure\Debug::getInstance();
        $aeSettings=\AeSecure\Settings::getInstance();

        $fullname=str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $aeSettings->getFolderDocs(true).
            ltrim($params['filename'], DS)
        );

        $arrDebug=array();
        /**/

        if ($params['type']==='folder') {

            // It's a folder

            if (!\AeSecure\Files::folderExists(utf8_decode($fullname))) {
                echo str_replace(
                    '%s',
                    '<strong>'.$fullname.'</strong>',
                    $aeSettings->getText('folder_not_found', 'The folder [%s] doesn\\&#39;t exists')
                );

                return;

            } else { // if (!\AeSecure\Files::folderExists($fullname))

                if (is_writable(utf8_decode($fullname))) {

                    try {
                        // Kill a folder recursivelly ==> be really sure that the folder is within the documents
                        // folder and not elsewhere

                        // $docs will be something like c:\websites\notes\docs\

                        $docs=$aeSettings->getFolderDocs(true);

                        // $fullname will be something like c:\websites\notes\docs\folder\folder_to_kill
                        // So be really sure that the $fullname absolute path is well within the $docs
                        // folder (strcmp should strictly give 0).  if so, continue and allow the deletion
                        // If not, stop and return an error.

                        if (strcmp($docs, substr($fullname, 0, strlen($docs)))===0) {
                            // Ok, recursively kill the folder and its content

                            $it = new \RecursiveDirectoryIterator(utf8_decode($fullname), \RecursiveDirectoryIterator::SKIP_DOTS);
                            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

                            foreach ($files as $file) {
                                if ($file->isDir()) {
                                    rmdir($file->getRealPath());

                                    if (!\AeSecure\Files::folderExists($file->getRealPath())) {
                                        /**/
                                    } else {
                                        /**/
                                    }
                                } else { // if ($file->isDir())

                                    unlink($file->getRealPath());

                                    if (!\AeSecure\Files::fileExists($file->getRealPath())) {
                                        /**/
                                    } else {
                                        /**/
                                    }
                                } // if ($file->isDir())
                            } // foreach

                            // And kill the folder itself

                            rmdir(utf8_decode($fullname));

                            /**/

                            $msg=sprintf(
                                $aeSettings->getText('folder_deleted', 'The folder [%s] and its content has been deleted'),
                                $params['filename']
                            );

                            echo \AeSecure\JSON::json_return_info(
                                array(
                                'status'=>1,
                                'action'=>'delete',
                                'type'=>$params['type'],
                                'msg'=>$msg
                                ),
                                $arrDebug
                            );
                        } else {
                            $msg=sprintf(
                                $aeSettings->getText('folder_not_deleted', 'The folder [%s] is outside your documentation root folder and therefore will not be deleted'),
                                $params['filename']
                            );

                            echo \AeSecure\JSON::json_return_info(
                                array(
                                'status'=>0,
                                'action'=>'delete',
                                'type'=>$params['type'],
                                'msg'=>$msg
                                ),
                                $arrDebug
                            );
                        }
                    } catch (Exception $ex) {
                        echo \AeSecure\JSON::json_return_info(
                            array(
                            'status'=>0,
                            'action'=>'delete',
                            'type'=>$params['type'],
                            'msg'=>$ex->getMessage()
                            ),
                            $arrDebug
                        );
                    } // try
                } else { // if(is_writable($fullname))

                    // The folder is readonly, can't delete it

                    $msg=sprintf(
                        $aeSettings->getText('folder_read_only', 'Sorry but the folder [%s] is read-only'),
                        $params['filename']
                    );

                    echo \AeSecure\JSON::json_return_info(
                        array(
                        'status'=>0,
                        'action'=>'delete',
                        'type'=>$params['type'],
                        'msg'=>$msg
                        ),
                        $arrDebug
                    );
                } // if(is_writable($fullname))
            } // if (!\AeSecure\Files::folderExists($fullname))
        } else { // if($params['type']==='folder')

            // It's a file

            // If the filename doesn't mention the file's extension, add it.
            if (substr($params['filename'], -3)!='.md') {
                $params['filename'].='.md';
            }

            if (!\AeSecure\Files::fileExists($fullname)) {
                echo str_replace(
                    '%s',
                    '<strong>'.$fullname.'</strong>',
                    $aeSettings->getText('file_not_found', 'The note [%s] doesn\\&#39;t exists')
                );

                die();
            } else { // if (!\AeSecure\Files::fileExists($fullname))

                if (is_writable($fullname)) {
                    try {
                        unlink($fullname);

                        if (!\AeSecure\Files::fileExists($fullname)) {
                            // The note was successfully deleted

                            // Check if there were .html versions of the note and if so, delete them
                            if (\AeSecure\Files::fileExists($fnameHTML = str_replace('.md', '.html', $fullname))) {
                                unlink($fnameHTML);
                            }

                            if (\AeSecure\Files::fileExists($fnameHTML = str_replace('.md', '_slideshow.html', $fullname))) {
                                unlink($fnameHTML);
                            }

                            $msg=sprintf(
                                $aeSettings->getText('file_deleted', 'The note [%s] has been successfully deleted'),
                                $params['filename']
                            );

                            echo \AeSecure\JSON::json_return_info(
                                array(
                                'status'=>1,
                                'action'=>'delete',
                                'type'=>$params['type'],
                                'msg'=>$msg
                                ),
                                $arrDebug
                            );
                        } else { // if (!\AeSecure\Files::fileExists($fullname))

                            // A problem has occured

                            $msg=sprintf(
                                $aeSettings->getText('error_delete_file', 'An error has occured during the deletion of the note [%s]'),
                                $params['filename']
                            );

                            echo \AeSecure\JSON::json_return_info(
                                array(
                                'status'=>0,
                                'action'=>'delete',
                                'type'=>$params['type'],
                                'msg'=>$msg
                                ),
                                $arrDebug
                            );
                        } // if (!\AeSecure\Files::fileExists($fullname))
                    } catch (Exception $ex) {
                        echo \AeSecure\JSON::json_return_info(
                            array(
                            'status'=>0,
                            'action'=>'delete',
                            'type'=>$params['type'],
                            'msg'=>$ex->getMessage()
                            ),
                            $arrDebug
                        );
                    } // try
                } else { // if(is_writable($fullname))

                    // The file is readonly, can't delete it

                    $msg=sprintf(
                        $aeSettings->getText('file_read_only', 'The note [%s] is read-only, it\\&#39;s then impossible to delete it'),
                        $params['filename']
                    );

                    echo \AeSecure\JSON::json_return_info(
                        array(
                        'status'=>0,
                        'action'=>'delete',
                        'type'=>$params['type'],
                        'msg'=>$msg
                        ),
                        $arrDebug
                    );
                } // if(is_writable($fullname))
            } // // if (!\AeSecure\Files::fileExists($fullname))
        } // if($params['type']==='folder')

        return;

    } // function Run()
} // class Display
