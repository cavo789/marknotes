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
        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            $arrDebug['debug'][]=$aeDebug->log(__METHOD__, true);
            $arrDebug['debug'][]=$aeDebug->log('Oldname '.utf8_encode($params['oldname']), true);
            $arrDebug['debug'][]=$aeDebug->log('Newname '.utf8_encode($params['newname']), true);
            $arrDebug['debug'][]=$aeDebug->log('Type '.$params['type'], true);
        }
        /*<!-- endbuild -->*/

        try {            
            if (!is_writable(dirname($params['oldname']))) {
                // The folder is read-only, we can't add new folder / notes
                $msg=str_replace('%s', utf8_encode(dirname($params['oldname'])),$aeSettings->getText('folder_read_only'));
                echo \AeSecure\JSON::json_return_info(array('status'=>0,'msg'=>$msg),$arrDebug);
            }
        } catch (Exception $ex) {
           echo \AeSecure\JSON::json_return_info(array('status'=>0,'msg'=>utf8_encode($ex->getMessage())),$arrDebug);
        } // try

        if ($params['type']==='folder') {
   
            // Operation on a folder : create or rename
      
            if (!\AeSecure\Files::folderExists($params['oldname'])) {
   
                // create a new folder (oldname is perhaps "c:\sites\notes\docs\new folder" while
                // newname can be "c:\sites\notes\docs\developments" i.e. the name given by the user in the 
                // frontend interface
                                
                try {
                    
                    mkdir($params['newname'], $aeSettings->getchmod('folder'));
                    
                    if (is_dir($params['newname'])) {
                        
                        $msg=str_replace(
                           '%s',
                           utf8_encode(str_replace($aeSettings->getFolderDocs(true),'',$params['newname'])),
                           $aeSettings->getText('folder_created')
                        );
                        
                        echo \AeSecure\JSON::json_return_info(array('status'=>1,'msg'=>$msg),$arrDebug);
                        
                    } else { // if (is_dir($params['newname']))
                        
                        $msg=str_replace('%s', $params['newname'], $aeSettings->getText('error_create_folder'));
                        echo \AeSecure\JSON::json_return_info(array('status'=>0,'msg'=>$msg),$arrDebug);
                        
                    } // if (is_dir($params['newname']))
                    
                } catch (Exception $ex) {
                    echo \AeSecure\JSON::json_return_info(array('status'=>0,'msg'=>$ex->getMessage()),$arrDebug);
                } // try
               
            } elseif (\AeSecure\Files::folderExists($params['oldname'])) {
  
                // Rename an existing folder
               
                rename($params['oldname'],$params['newname']);

                if (is_dir($params['newname'])) {
                    
                    $msg=sprintf(
                       $params['folder_renamed'],
                       utf8_encode(str_replace($aeSettings->getFolderDocs(true),'',$params['oldname'])),
                       utf8_encode(str_replace($aeSettings->getFolderDocs(true),'',$params['newname']))
                    );

                    echo \AeSecure\JSON::json_return_info(array('status'=>1,'msg'=>$msg),$arrDebug);
                    
                } else { // if (is_dir($params['newname']))
                  
                    $msg=sprintf(
                       $params['error_rename_folder'],
                       utf8_encode(str_replace($params['folder'],'',$params['oldname'])),
                       utf8_encode(str_replace($params['folder'],'',$params['newname']))
                    );
                             
                    echo \AeSecure\JSON::json_return_info(array('status'=>0,'msg'=>$ex->getMessage()),$arrDebug);

                } // if (is_dir($params['newname']))
            }
       
        } else { // if ($params['type']==='folder')
       
            // Operation on a file : create or rename
            
            // It's a file, be sure to have the .md extension
            $params['oldname']=\AeSecure\Files::removeExtension($params['oldname']).'.md';
            $params['newname']=\AeSecure\Files::removeExtension($params['newname']).'.md';

            if (!\AeSecure\Files::fileExists($params['oldname'])) {
         
                // Define the filename
                $params['newname']=\AeSecure\Files::removeExtension(\AeSecure\Files::sanitizeFileName($params['newname'])).'.md';
                
                // Define the content : get the filename without the extension and set the content as heading 1
                $content='# '.basename(\AeSecure\Files::removeExtension($params['newname'])).PHP_EOL;
              
                \AeSecure\Files::createFile($params['newname'], $content, $aeSettings->getchmod('file'));
                
                if (\AeSecure\Files::fileExists($params['newname'])) {

                    $msg=str_replace(
                       '%s', 
                       utf8_encode(str_replace($aeSettings->getFolderDocs(true),'',$params['newname'])), 
                       $aeSettings->getText('file_created')
                    );
                  
                    echo \AeSecure\JSON::json_return_info(
                       array(
                          'status'=>1,
                          'msg'=>$msg,
                          'filename'=>utf8_encode(str_replace($aeSettings->getFolderDocs(true), '', $params['newname']))
                        ),$arrDebug);
                      
                } else { // if (\AeSecure\Files::fileExists($params['newname'])) 
                 
                    $msg=str_replace('%s', $params['newname'], $aeSettings->getText('error_create_file', 'An error has occured during the creation of the file [%]'));
                    echo \AeSecure\JSON::json_return_info(array('status'=>0,'msg'=>$msg),$arrDebug);
                        
                } // if (\AeSecure\Files::fileExists($params['newname'])) 
                
            } elseif (\AeSecure\Files::fileExists(utf8_decode($params['oldname']))) {
                   
                rename(utf8_decode($params['oldname']),utf8_decode($params['newname']));
                
                if (\AeSecure\Files::fileExists($params['newname'])) {
                    
                    $msg=sprintf(
                       $aeSettings->getText('file_renamed','The file [%s] has been renamed into [%s]'),
                       utf8_encode(str_replace($aeSettings->getFolderDocs(true),'',$params['oldname'])),
                       utf8_encode(str_replace($aeSettings->getFolderDocs(true),'',$params['newname']))
                    );

                    echo \AeSecure\JSON::json_return_info(array('status'=>1,'msg'=>$msg),$arrDebug);
                    
                } else { // if (is_dir($params['newname']))
                  
                    $msg=sprintf(
                       $aeSettings->getText('error_rename_file','An error has occured when trying to rename the file [%] into [%s]'),
                       utf8_encode(str_replace($aeSettings->getFolderDocs(true),'',$params['oldname'])),
                       utf8_encode(str_replace($aeSettings->getFolderDocs(true),'',$params['newname']))
                    );
                             
                    echo \AeSecure\JSON::json_return_info(array('status'=>0,'msg'=>$msg),$arrDebug);

                } // if (is_dir($params['newname']))
                
            }
                 
        } // if ($params['type']==='folder')
        
        die();
        
    } // function Run()
    
} // class AddOrRename