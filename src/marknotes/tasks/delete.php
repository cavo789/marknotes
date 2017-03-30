<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

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

        $aeDebug=\MarkNotes\Debug::getInstance();
        $aeSettings=\MarkNotes\Settings::getInstance();

        $fullname=str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $aeSettings->getFolderDocs(true).
            ltrim($params['filename'], DS)
        );

        $arrDebug=array();
        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            $arrDebug['debug'][]=$aeDebug->log(__METHOD__, true);
            $arrDebug['debug'][]=$aeDebug->log('Filename '.$params['filename'], true);
            $arrDebug['debug'][]=$aeDebug->log('Type '.$params['type'], true);
        }
        /*<!-- endbuild -->*/

        if ($params['type']==='folder') {
            // It's a folder

            if (!\MarkNotes\Files::folderExists(utf8_decode($fullname))) {
                echo str_replace(
                    '%s',
                    '<strong>'.$fullname.'</strong>',
                    $aeSettings->getText('folder_not_found', 'The folder [%s] doesn\\&#39;t exists')
                );

                return;
            } else { // if (!\MarkNotes\Files::folderExists($fullname))

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

                                    if (!\MarkNotes\Files::folderExists($file->getRealPath())) {
                                        /*<!-- build:debug -->*/
                                        if ($aeSettings->getDebugMode()) {
                                            $arrDebug['debug'][]=$aeDebug->log('Kill folder '.utf8_encode($file->getRealPath()), true);
                                        }
                                        /*<!-- endbuild -->*/
                                    } else {
                                        /*<!-- build:debug -->*/
                                        if ($aeSettings->getDebugMode()) {
                                            $arrDebug['debug'][]=$aeDebug->log('Folder is read-only and can\'t be removed : '.utf8_encode($file->getRealPath()), true);
                                        }
                                        /*<!-- endbuild -->*/
                                    }
                                } else { // if ($file->isDir())

                                    unlink($file->getRealPath());

                                    if (!\MarkNotes\Files::fileExists($file->getRealPath())) {
                                        /*<!-- build:debug -->*/
                                        if ($aeSettings->getDebugMode()) {
                                            $arrDebug['debug'][]=$aeDebug->log('Kill file '.utf8_encode($file->getRealPath()), true);
                                        }
                                        /*<!-- endbuild -->*/
                                    } else {
                                        /*<!-- build:debug -->*/
                                        if ($aeSettings->getDebugMode()) {
                                            $arrDebug['debug'][]=$aeDebug->log('File is read-only and can\'t be removed '.utf8_encode($file->getRealPath()), true);
                                        }
                                        /*<!-- endbuild -->*/
                                    }
                                } // if ($file->isDir())
                            } // foreach

                            // And kill the folder itself

                            rmdir(utf8_decode($fullname));

                            /*<!-- build:debug -->*/
                            if ($aeSettings->getDebugMode()) {
                                $arrDebug['debug'][]=$aeDebug->log('Kill folder '.$fullname, true);
                            }
                            /*<!-- endbuild -->*/

                            $msg=sprintf(
                                $aeSettings->getText('folder_deleted', 'The folder [%s] and its content has been deleted'),
                                $params['filename']
                            );

                            echo \MarkNotes\JSON::json_return_info(
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

                            echo \MarkNotes\JSON::json_return_info(
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
                        echo \MarkNotes\JSON::json_return_info(
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

                    echo \MarkNotes\JSON::json_return_info(
                        array(
                        'status'=>0,
                        'action'=>'delete',
                        'type'=>$params['type'],
                        'msg'=>$msg
                        ),
                        $arrDebug
                    );
                } // if(is_writable($fullname))
            } // if (!\MarkNotes\Files::folderExists($fullname))
        } else { // if($params['type']==='folder')

            // It's a file

            // If the filename doesn't mention the file's extension, add it.
            if (substr($params['filename'], -3)!='.md') {
                $params['filename'].='.md';
            }

            if (!\MarkNotes\Files::fileExists($fullname)) {
                echo str_replace(
                    '%s',
                    '<strong>'.$fullname.'</strong>',
                    $aeSettings->getText('file_not_found', 'The note [%s] doesn\\&#39;t exists')
                );

                die();
            } else { // if (!\MarkNotes\Files::fileExists($fullname))

                if (is_writable($fullname)) {
                    try {
                        unlink($fullname);

                        if (!\MarkNotes\Files::fileExists($fullname)) {
                            // The note was successfully deleted

                            // Check if there were .html versions of the note and if so, delete them
                            if (\MarkNotes\Files::fileExists($fnameHTML = str_replace('.md', '.html', $fullname))) {
                                unlink($fnameHTML);
                            }

                            if (\MarkNotes\Files::fileExists($fnameHTML = str_replace('.md', '_slideshow.html', $fullname))) {
                                unlink($fnameHTML);
                            }

                            $msg=sprintf(
                                $aeSettings->getText('file_deleted', 'The note [%s] has been successfully deleted'),
                                $params['filename']
                            );

                            echo \MarkNotes\JSON::json_return_info(
                                array(
                                'status'=>1,
                                'action'=>'delete',
                                'type'=>$params['type'],
                                'msg'=>$msg
                                ),
                                $arrDebug
                            );
                        } else { // if (!\MarkNotes\Files::fileExists($fullname))

                            // A problem has occured

                            $msg=sprintf(
                                $aeSettings->getText('error_delete_file', 'An error has occured during the deletion of the note [%s]'),
                                $params['filename']
                            );

                            echo \MarkNotes\JSON::json_return_info(
                                array(
                                'status'=>0,
                                'action'=>'delete',
                                'type'=>$params['type'],
                                'msg'=>$msg
                                ),
                                $arrDebug
                            );
                        } // if (!\MarkNotes\Files::fileExists($fullname))
                    } catch (Exception $ex) {
                        echo \MarkNotes\JSON::json_return_info(
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

                    echo \MarkNotes\JSON::json_return_info(
                        array(
                        'status'=>0,
                        'action'=>'delete',
                        'type'=>$params['type'],
                        'msg'=>$msg
                        ),
                        $arrDebug
                    );
                } // if(is_writable($fullname))
            } // // if (!\MarkNotes\Files::fileExists($fullname))
        } // if($params['type']==='folder')

        return;
    } // function Run()
} // class Display
