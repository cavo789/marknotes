<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

/**
* Save the new content of the file.   This function is called by the "Save" button available in the JS editor
*/

class Save
{

    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new Save();
        }

        return self::$_instance;
    }

    public function run(array $params)
    {

        $aeFiles=\MarkNotes\Files::getInstance();
        $aeSettings=\MarkNotes\Settings::getInstance();

        $return=array();

        if (!$aeSettings->getEditAllowed()) {
            $return=array('status'=>array('success'=>0,'message'=>$aeSettings->getText('no_save_allowed', 'Error, saving notes isn&#39;t allowed')));
        } else { // if (!$aeSettings->getEditAllowed())

            // If the filename doesn't mention the file's extension, add it.
            if (substr($params['filename'], -3)!='.md') {
                $params['filename'].='.md';
            }

            $fullname=str_replace('/', DIRECTORY_SEPARATOR, $aeSettings->getFolderDocs(true).utf8_decode(ltrim($params['filename'], DS)));

            // Initialize the encryption class
            $aesEncrypt=new \MarkNotes\Encrypt($aeSettings->getEncryptionPassword(), $aeSettings->getEncryptionMethod());

            // bReturn will be set on TRUE when the file has been rewritten (when <encrypt> content has been found)
            // $markdown will contains the new content (once encryption has been done)
            $params['markdown']=$aesEncrypt->HandleEncryption($fullname, $params['markdown']);

            // $bReturn is on FALSE when HandleEncryption hasn't found any <encrypt> tag => save the new content (otherwise already done by HandleEncryption)
            if (!$bReturn) {
                $bReturn=$aeFiles->rewriteFile($fullname, $params['markdown']);
            }

            if ($bReturn===true) {
                // The new content has been created, check if the .html version exists and if so, remove that old file
                if (file_exists($fnameHTML = $aeFiles->replaceExtension($fullname, 'html'))) {
                    @unlink($fnameHTML);
                }
                if (file_exists($fnameHTML = str_replace('.html', '_slideshow.html', $fnameHTML))) {
                    @unlink($fnameHTML);
                }

                $status=array('success'=>1,'message'=>$aeSettings->getText('button_save_done', 'The file has been successfully saved'));
            } else { // if ($status==true)

                // There is a problem
                $status=array('success'=>0,'message'=>str_replace('%s', $fullname, $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists')));
            } // if (file_exists($fullname))

            $return['status']=$status;
            $return['filename']=$fullname;
        } // if (!$aeSettings->getEditAllowed())

        return json_encode($return, JSON_PRETTY_PRINT);
    }
}
