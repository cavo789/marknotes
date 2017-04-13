<?php

namespace MarkNotes\Tasks\Converter;

defined('_MARKNOTES') or die('No direct access allowed');

class Decktape
{
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new Decktape();
        }

        return self::$_Instance;
    }

    /**
     * Convert a HTML to PDF thanks to decktape
     */
    public static function run(array $params) : string {

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // $sScriptName string Absolute filename to the phantomjs.exe script
        $arrDecktape = $aeSettings->getConvert('decktape');
        $sScriptName= $arrDecktape['script'];

        $aeTask = \MarkNotes\Tasks\PDF::getInstance();

        // Get the temporary name for the HTML and PDF files
        list($tmpHTML, $tmpPDF) = $aeTask->getTempNames($params);

        // Success the PDF exists
        $finalPDF = $aeTask->getPDFFileName($params['filename']);

        // Get the HTML version of the note
        $layout = $params['layout'];

        $aeTask = \MarkNotes\Tasks\Slideshow::getInstance();
        $html = $aeTask->run($params);

        // And store that HTML to the disk
        file_put_contents($tmpHTML, $html);

        // Get the type of slideshow (reveal or remark)
        $type = $aeSettings->getSlideshowType();

        // Create a script on the disk
        // Phantomjs (used by the Decktape conversion) should be started from the folder where the
        // HTML file to convert stay so use the Windows PUSH instruction to change the default directory
        // the time needed to run the script

        // Be carefull : decktape don't like accentuated characters
        // names below (to the html and pdf file) shouldn't contains any accentuated charcters
        $sProgram =
            'pushd "'.dirname($tmpHTML).'"'.PHP_EOL.
            $sScriptName.' '.dirname($sScriptName).DS.'decktape.js '.$type.' "'.basename($tmpHTML).'"'.
            ' "'.basename($tmpPDF).'"'.PHP_EOL;

        $slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($params['filename'])));

        $fScriptFile = dirname($tmpHTML).DS.$slug.'.bat';

        $aeFiles->fwriteANSI($fScriptFile, $sProgram);

        // Run the script. This part can be long depending on the number of slides in the HTML file to convert
        $output = array();

        exec($fScriptFile, $output);

        /*<!-- build:debug -->*/
        if ($aeSettings->getDebugMode()) {
            // $output is an array and contains the result of the script. If at least one line of the output start with
            // Error:, show the debug information and stop the code
            foreach ($output as $line) {
                if (substr($line, 0, 6) === 'Error:') {
                    die("<pre style='background-color:orange;'>".__FILE__." - ".__LINE__."<br/>There is an error with the deckTape script<br/><br/>".print_r($output, true)."</pre>");
                }
            }
        }
        /*<!-- endbuild -->*/

        try {

            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                // Do nothing i.e. keep the temporary files
            } else {
                /*<!-- endbuild -->*/
                unlink($tmpHTML);
                unlink($fscript);
                /*<!-- build:debug -->*/
            }
            /*<!-- endbuild -->*/

            if ($aeFiles->fileExists($tmpPDF)) {
                // Remane the temporary with its final name
                // Note : the PDF file was perhaps already moved by the convert script
                $aeFiles->renameFile($tmpPDF, $finalPDF);
            }
        } catch (Exception $e) {
            $finalPDF = '';
        }

        return $finalPDF;

    }
}
