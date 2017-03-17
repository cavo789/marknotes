<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-02-16T12:37:19.426Z
*/?>
<?php

namespace AeSecureMDTasks;

/**
 * Create file/folder or rename file/folder
 */

class AddOrRename
{
    public static function Run(array $params)
    {

        header('Content-Type: application/json');
        
        $aeDebug=\AeSecure\Debug::getInstance();
        $aeSettings=\AeSecure\Settings::getInstance();
        
        $params['oldname']=$aeSettings->getFolderDocs(true).$params['oldname'];
        $params['newname']=$aeSettings->getFolderDocs(true).$params['newname'];

        $arrDebug=array();
        /**/

        try {
            // use utf8_decode since the name can contains accentuated characters
            if (!is_writable(utf8_decode(dirname($params['oldname'])))) {
                // The folder is read-only, we can't add new folder / notes
                $msg = str_replace(
                    '%s',
                    utf8_encode(dirname($params['oldname'])),
                    $aeSettings->getText('folder_read_only', 'Sorry but the folder [%s] is read-only, no new folder or note can&#39;t be added there')
                );
                echo \AeSecure\JSON::json_return_info(array('status'=>0,'msg'=>$msg), $arrDebug);
            }
        } catch (Exception $ex) {
            echo \AeSecure\JSON::json_return_info(array('status'=>0,'msg'=>utf8_encode($ex->getMessage())), $arrDebug);
        } // try

        if ($params['type']==='folder') {
            // Operation on a folder : create or rename
      
            if (!\AeSecure\Files::folderExists(utf8_decode($params['oldname']))) {
                // create a new folder (oldname is perhaps "c:\sites\notes\docs\new folder" while
                // newname can be "c:\sites\notes\docs\developments" i.e. the name given by the user in the
                // frontend interface
                                
                try {
                    mkdir(utf8_decode($params['newname']), $aeSettings->getchmod('folder'));
                    
                    if (\AeSecure\Files::folderExists(utf8_decode($params['newname']))) {
                        $msg=str_replace(
                            '%s',
                            str_replace($aeSettings->getFolderDocs(true), '', $params['newname']),
                            $aeSettings->getText('folder_created', 'The folder [%s] has been created on the disk')
                        );
                        
                        echo \AeSecure\JSON::json_return_info(array(
                                'status'=>1,
                                'action'=>'create',
                                'type'=>$params['type'],
                                'msg'=>$msg
                            ), $arrDebug);
                    } else { // if (is_dir($params['newname']))
                        
                        $msg = str_replace(
                            '%s',
                            $params['newname'],
                            $aeSettings->getText('error_create_folder', 'An error has occured during the creation of the folder [%s]')
                        );
                        
                        echo \AeSecure\JSON::json_return_info(array(
                                'status'=>0,
                                'action'=>'create',
                                'type'=>$params['type'],
                                'msg'=>$msg
                            ), $arrDebug);
                    } // if (is_dir($params['newname']))
                } catch (Exception $ex) {
                    echo \AeSecure\JSON::json_return_info(array(
                            'status'=>0,
                            'action'=>'create',
                            'type'=>$params['type'],
                            'msg'=>$ex->getMessage()
                        ), $arrDebug);
                } // try
            } elseif (\AeSecure\Files::folderExists(utf8_decode($params['oldname']))) {
                // Rename an existing folder

                rename(utf8_decode($params['oldname']), utf8_decode($params['newname']));

                if (\AeSecure\Files::folderExists(utf8_decode($params['newname']))) {
                    $msg=sprintf(
                        $aeSettings->getText('folder_renamed', 'The folder [%s] has been renamed into [%s]'),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['oldname']),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['newname'])
                    );

                    echo \AeSecure\JSON::json_return_info(array(
                            'status'=>1,
                            'action'=>'rename',
                            'type'=>$params['type'],
                            'msg'=>$msg
                        ), $arrDebug);
                } else { // if (is_dir($params['newname']))
                  
                    $msg=sprintf(
                        $aeSettings->getText('error_rename_folder', 'An error has occured when trying to rename the folder [%s] into [%s]'),
                        str_replace($params['folder'], '', $params['oldname']),
                        str_replace($params['folder'], '', $params['newname'])
                    );
                             
                    echo \AeSecure\JSON::json_return_info(array(
                            'status'=>0,
                            'action'=>'rename',
                            'type'=>$params['type'],
                            'msg'=>$msg
                        ), $arrDebug);
                } // if (is_dir($params['newname']))
            }
        } else { // if ($params['type']==='folder')
       
            // Operation on a file : create or rename
            
            // It's a file, be sure to have the .md extension
            $params['oldname']=\AeSecure\Files::removeExtension($params['oldname']).'.md';
            $params['newname']=\AeSecure\Files::removeExtension($params['newname']).'.md';

            if (!\AeSecure\Files::fileExists(utf8_decode($params['oldname']))) {
                // Define the filename
                $params['newname']=\AeSecure\Files::removeExtension(\AeSecure\Files::sanitizeFileName($params['newname'])).'.md';
                
                // Define the content : get the filename without the extension and set the content as heading 1
                $content='# '.basename(\AeSecure\Files::removeExtension($params['newname'])).PHP_EOL;
              
                \AeSecure\Files::createFile(utf8_decode($params['newname']), $content, $aeSettings->getchmod('file'));
                
                if (\AeSecure\Files::fileExists(utf8_decode($params['newname']))) {
                    $msg=str_replace(
                        '%s',
                        str_replace($aeSettings->getFolderDocs(true), '', $params['newname']),
                        $aeSettings->getText('file_created', 'The note [%s] has been successfully created on the disk')
                    );
                  
                    echo \AeSecure\JSON::json_return_info(
                        array(
                            'status'=>1,
                            'action'=>'create',
                            'type'=>$params['type'],
                            'msg'=>$msg,
                            'filename'=>str_replace($aeSettings->getFolderDocs(true), '', $params['newname'])
                        ),
                        $arrDebug
                    );
                } else { // if (\AeSecure\Files::fileExists($params['newname']))
                 
                    $msg=str_replace(
                        '%s',
                        $params['newname'],
                        $aeSettings->getText('error_create_file', 'An error has occured during the creation of the note [%s]')
                    );
                    
                    echo \AeSecure\JSON::json_return_info(
                        array(
                            'status'=>0,
                            'action'=>'create',
                            'type'=>$params['type'],
                            'msg'=>$msg
                        ),
                        $arrDebug
                    );
                } // if (\AeSecure\Files::fileExists($params['newname']))
            } elseif (\AeSecure\Files::fileExists(utf8_decode($params['oldname']))) {
                rename(utf8_decode($params['oldname']), utf8_decode($params['newname']));
                
                if (\AeSecure\Files::fileExists(utf8_decode($params['newname']))) {
                    // Check if there were .html versions of the note and if so, kill them (will be recreated)
                    if (\AeSecure\Files::fileExists(utf8_decode($fnameHTML = str_replace('.md', '.html', $params['oldname'])))) {
                        unlink(utf8_decode($fnameHTML));
                    }
                    if (\AeSecure\Files::fileExists(utf8_decode($fnameHTML = str_replace('.md', '_slideshow.html', $params['oldname'])))) {
                        unlink(utf8_decode($fnameHTML));
                    }
                            
                    $msg=sprintf(
                        $aeSettings->getText('file_renamed', 'The note [%s] has been renamed into [%s]'),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['oldname']),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['newname'])
                    );

                    echo \AeSecure\JSON::json_return_info(array(
                            'status'=>1,
                            'action'=>'rename',
                            'type'=>$params['type'],
                            'msg'=>$msg
                        ), $arrDebug);
                } else { // if (is_dir($params['newname']))
                  
                    $msg=sprintf(
                        $aeSettings->getText('error_rename_file', 'An error has occured when trying to rename the note [%s] into [%s]'),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['oldname']),
                        str_replace($aeSettings->getFolderDocs(true), '', $params['newname'])
                    );
                             
                    echo \AeSecure\JSON::json_return_info(array(
                            'status'=>0,
                            'action'=>'rename',
                            'type'=>$params['type'],
                            'msg'=>$msg
                        ), $arrDebug);
                } // if (is_dir($params['newname']))
            }
        } // if ($params['type']==='folder')
        
        die();
    } // function Run()
} // class AddOrRename
