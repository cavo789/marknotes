<?php

namespace MarkNotes\Tasks\Converter;

defined('_MARKNOTES') or die('No direct access allowed');

class Pandoc
{
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new Pandoc();
        }

        return self::$_Instance;
    }

    /**
     * Convert a HTML to an another format (docx, pdf, ...) thanks to pandoc
     */
    public static function run(array $params) : string {

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // $sScriptName string Absolute filename to the pandoc.exe script
        $arrPandoc = $aeSettings->getConvert('pandoc');
        $sScriptName= $arrPandoc['script'];

        $aeTask = \MarkNotes\Tasks\PDF::getInstance();

        $final = $aeTask->getFileName($params['filename'],$params['task']);

        // $params['task'] is the output format (f.i. pdf), check if there are options to use
        // for that format
        $options = isset($arrPandoc['options'][$params['task']]) ? $arrPandoc['options'][$params['task']] : '';

        // Create a script on the disk
        $sProgram =
            'pushd "'.dirname($aeSettings->getFolderDocs(true).$params['filename']).'"'.PHP_EOL.
            $sScriptName.' -s '. $options . ' -o "'.$final.'" '.
            '"'.$aeSettings->getFolderDocs(true).$params['filename'].'"'.PHP_EOL;

        $slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($params['filename'])));

        $fScriptFile = $aeSettings->getFolderTmp().$slug.'.bat';

        $aeFiles->fwriteANSI($fScriptFile, $sProgram);

        echo '<pre>'.$sProgram.'</pre>';

        // Run the script. This part can be long depending on the number of slides in the HTML file to convert
        $output = array();

        exec($fScriptFile, $output);

        /*<!-- build:debug -->*/
        /*if ($aeSettings->getDebugMode()) {
            // $output is an array and contains the result of the script. If at least one line of the output start with
            // Error:, show the debug information and stop the code
            foreach ($output as $line) {
                if (substr($line, 0, 6) === 'Error:') {
                    die("<pre style='background-color:orange;'>".__FILE__." - ".__LINE__."<br/>There is an error with the pandoc script<br/><br/>".print_r($output, true)."</pre>");
                }
            }
        }*/
        /*<!-- endbuild -->*/

        try {

            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                // Do nothing i.e. keep the temporary files
            } else {
                /*<!-- endbuild -->*/
                unlink($fscript);
                /*<!-- build:debug -->*/
            }
            /*<!-- endbuild -->*/

        } catch (Exception $e) {
        }

        return $final;

    }
}
