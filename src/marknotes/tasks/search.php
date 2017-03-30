<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

/**
* Search for "$keywords" in the filename or in the file content.  Stop on the first occurence to speed up
* the process.
*
* Note : if the content contains encrypted data, the data is decrypted so the search can be done on these info too
*/

class Search
{
    protected static $_instance = null;

    public function __construct()
    {
        return true;
    }

    public function getInstance()
    {

        if (self::$_instance === null) {
            self::$_instance = new Search();
        }

        return self::$_instance;
    }

    public function run(array $params)
    {

        $aeFiles=\MarkNotes\Files::getInstance();
        $aeFunctions=\MarkNotes\Functions::getInstance();
        $aeSettings=\MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        $return=array();
        if (trim($params['pattern'])=='') {
            return $return;
        }

        // $keywords can contains multiple terms like 'invoices,2017,internet'.
        // Search for these three keywords (AND)
        $keywords=explode(',', rtrim($params['pattern'], ','));

        $arrFiles=array();
        if ($aeSettings->getOptimisationUseServerSession()) {
            // Get the list of files/folders from the session object if possible
            $arrFiles=json_decode($aeSession->get('SearchFileList', ''));
            if (!is_array($arrFiles)) {
                $arrFiles=array();
            }
        }

        if (count($arrFiles)==0) {
            $arrFiles=$aeFunctions->array_iunique($aeFiles->rglob('*.md', $aeSettings->getFolderDocs(true)));

            // Sort, case insensitve
            natcasesort($arrFiles);

            // Be carefull, folders / filenames perhaps contains accentuated characters
            $arrFiles=array_map('utf8_encode', $arrFiles);

            if ($aeSettings->getOptimisationUseServerSession()) {
                // Remember for the next call
                $aeSession->set('SearchFileList', json_encode($arrFiles, JSON_PRETTY_PRINT));
            }
        }

        if (count($arrFiles)==0) {
            return null;
        }

        // docs should be relative so $aeSettings->getFolderDocs(false) and not $aeSettings->getFolderDocs(true)
        $docs=str_replace('/', DS, $aeSettings->getFolderDocs(false));

        $aeEncrypt=\MarkNotes\Encrypt::getInstance($aeSettings->getEncryptionPassword(), $aeSettings->getEncryptionMethod());

        foreach ($arrFiles as $file) {
            // Don't mention the full path, should be relative for security reason
            $file=str_replace($aeSettings->getFolderDocs(true), '', $file);

            // If the keyword can be found in the document title, yeah, it's the fatest solution,
            // return that filename

            foreach ($keywords as $keyword) {
                $bFound=true;
                if (stripos($file, $keyword)===false) {
                    // at least one term is not present in the filename, stop
                    $bFound=false;
                    break;
                }
            } // foreach($keywords as $keyword)

            if ($bFound) {
                // Found in the filename => stop process of this file
                $return[]=md5($docs.$file);
            } else { // if ($bFound)

                // Open the file and check against its content (plain and encrypted)
                $fullname=utf8_decode($aeSettings->getFolderDocs(true).$file);
                $content=file_get_contents($fullname);

                // Verify if the note contains encrypted data and, if so, decrypt them first

                $matches = array();

                // ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
                preg_match_all('/<encrypt[[:blank:]]*([^>]*)>([\\S\\n\\r\\s]*?)<\/encrypt>/', $content, $matches);

                // If matches is greater than zero, there is at least one <encrypt> tag found in the file content
                if (count($matches[1])>0) {
                    $j=count($matches[0]);

                    $i=0;

                    // Loop and process every <encrypt> tags
                    // For instance : <encrypt data-encrypt="true">ENCRYPTED TEXT</encrypt>

                    for ($i; $i<$j; $i++) {
                        // Retrieve the attributes (f.i. data-encrypt="true")
                        $attributes=$matches[1][$i];

                        // Are there data-encrypt=true content ? If yes, unencrypt it
                        $tmp=array();
                        preg_match('#data-encrypt="(.*)"#', $attributes, $tmp);

                        if (count($tmp)>0) {
                            // Only when data-encrypt="true" is found, consider the content has an encrypted one.
                            $isEncrypted=(strcasecmp(rtrim($tmp[1]), 'true')===0?true:false);
                            $decrypt=$aeEncrypt->sslDecrypt($matches[2][$i], null);
                            $content=str_replace($matches[2][$i], $decrypt, $content);
                        }
                    } // for($i;$i<$j;$i++)
                } // if (count($matches[1])>0) {

                $bFound=true;

                foreach ($keywords as $keyword) {
                    if (stripos($content, $keyword)===false) {
                        // at least one term is not present in the content, stop
                        $bFound=false;
                        break;
                    }
                } // foreach($keywords as $keyword)

                if ($bFound) {
                    // Found in the filename => stop process of this file
                    $return[]=md5($docs.$file);
                }  // if ($bFound)
            } // if ($bFound) {
        } // foreach ($arrFiles as $file)

        return json_encode($return, JSON_PRETTY_PRINT);
    }
}
