<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class PDF
{
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new PDF();
        }

        return self::$_Instance;
    }

    /**
     * Taking the name of the note, provide the name of the .pdf to create
     * F.i. for c:\sites\marknotes\docs\so nice app.md returns  c:\sites\marknotes\docs\so_nice_app.pdf
     */
    private function getPDFFileName(string $fname) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $fname = $aeFiles->replaceExtension(
            str_replace(
                '/',
                DS,
                utf8_decode(
                    $aeSettings->getFolderDocs(true).
                    ltrim($fname, DS)
                )
            ),
            'pdf'
        );

        return $fname;
    }

    /**
     * Ask for a folder name (f.i. c:\sites\marknotes\docs\) and return two temporary filenames; one with
     * the .html extension and the second with .pdf
     */
    private function getTempNames(array $params) : array
    {
        $tmpHTML = '';
        $tmpPDF = '';

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $tmp = $aeSettings->getFolderTmp();
        $slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($params['filename'])));

        $tmpHTML = $tmp.$slug.'.html';
        $tmpPDF = $tmp.$slug.'.pdf';

        return array($tmpHTML, $tmpPDF);
    }

    /**
     * rename will first remove the existing "new" file if the file already exists
     */
    private function renamePDF(string $old, string $new) : bool
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        // Remove the old version if already there
        if ($aeFiles->fileExists($new)) {
            unset($new);
        }

        // And rename the temporary PDF to its final name
        rename($old, $new);

        return true;
    }

    public function download(string $fname) : bool
    {
        $bReturn = false;

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($fname))).'.pdf';

        if ($aeFiles->fileExists($fname)) {
            // And send the file to the browser
            header('Content-Type: application/pdf');
            header('Content-Disposition: download; filename="'.$slug.'"');
            header('Content-Length: '.filesize(utf8_decode($fname)));
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');

            @readfile(utf8_decode($fname));

            $bReturn = true;
        } else { // if ($content!=='')

            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                echo __FILE__."-".__LINE__." - The file ".$fname." doesn't exists<br/>";
            }
            /*<!-- endbuild -->*/

            $bReturn = false;
        }
        return $bReturn;
    }

    /**
     * Use the domPDF library for the conversion HTML -> PDF
     * On a Windows server, deckTape give amazing results but also slower
     */
    private function domPDF(array $params) : string
    {
        $finalPDF = $this->getPDFFileName($params['filename']);

        // Use the pdf template and not the "html" one
        $params['task'] = 'pdf';
        $params['template'] = 'pdf';

        $aeTask = \MarkNotes\Tasks\Display::getInstance();
        $html = $aeTask->run($params);

        $dompdf = new \Dompdf\Dompdf();

        $dompdf->set_base_path(dirname($finalPDF).DS);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();
        unset($dompdf);

        file_put_contents($finalPDF, $output);

        return $finalPDF;
    }

    /**
     * Convert a HTML to PDF thanks to decktape
     *
     * $sDecktape string Absolute filename to the phantomjs.exe script
     */
    private function deckTape(string $sDecktape, array $params) : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // Get the temporary name for the HTML and PDF files
        list($tmpHTML, $tmpPDF) = $this->getTempNames($params);

        // Success the PDF exists
        $finalPDF = $this->getPDFFileName($params['filename']);

        // Get the HTML version of the note
        $layout = $params['layout'];

        // Depending on the layout (html (i.e. article) or slideshow), call the correct task
        if ($layout === 'html') {
            $aeTask = \MarkNotes\Tasks\Display::getInstance();
        } else {
            $aeTask = \MarkNotes\Tasks\Slideshow::getInstance();
        }

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
            $sDecktape.' '.dirname($sDecktape).DS.'decktape.js '.$type.' "'.basename($tmpHTML).'"'.
            ' "'.basename($tmpPDF).'"'.PHP_EOL.
            'copy "'.basename($tmpPDF).'" "'.$finalPDF.'"';

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
                // Note : the PDF file was perhaps already moved
                $this->renamePDF($tmpPDF, $finalPDF);
            }
        } catch (Exception $e) {
            $finalPDF = '';
        }

        return $finalPDF;
    }

    public function run(array $params)
    {

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3) != '.md') {
            $params['filename'] .= '.md';
        }

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $fPDF = '';
        $layout = isset($params['layout']) ? $params['layout'] : '';

        // Retrieve the fullname of the PDF
        $fPDF = $aeSettings->getFolderDocs(true).$aeFiles->replaceExtension($params['filename'], 'pdf');

        // And check if the PDF file already exists => faster than creating the PDF on-the-fly
        if ($aeFiles->fileExists($fPDF)) {
            $fMD = $aeSettings->getFolderDocs(true).$aeFiles->replaceExtension($params['filename'], 'md');
            if (filemtime($fPDF) < filemtime($fMD)) {
                // The note has been modified after the generation of the .pdf => no more up-to-date
                $fPDF = '';
            }
        }

        // Doesn't exists yet ? Create it
        if (($fPDF === '') || (!$aeFiles->fileExists($fPDF))) {
            // Check if, by luck, decktape is installed but only under Windows OS
            $sDecktape = '';

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // deckTape is only for slideshow view and not for HTML view
                if (in_array($layout, array('reveal', 'remark'))) {
                    $aeFunctions = \MarkNotes\Functions::getInstance();

                    // The exec() function should be enabled to use deckTape
                    if (!$aeFunctions->ifDisabled('exec')) {
                        $sDecktape = $aeSettings->getTools('decktape');
                    }
                }
            } // if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')

            if ($sDecktape !== '') {
                // YES ! Decktape is there, use it
                $fname = $this->deckTape($sDecktape, $params);
            } else {
                // No, use dompdf but the result isn't so good
                if (is_dir($aeSettings->getFolderLibs()."dompdf")) {
                    $fname = $this->domPDF($params);
                } // if (file_exists($fullname))
            } // if ($sDecktape!=='')
        }

        // Return the fullname of the PDF file
        return $fPDF;
    }
}
