<?php

namespace MarkNotes\Plugins\Content\PDF;

defined('_MARKNOTES') or die('No direct access allowed');

class DeckTape
{
    /**
     *
     */
    public static function doIt(&$params = null) : bool
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // DeckTape is only for slides : reveal or remark and not for "normal" html rendering

        $layout = $params['layout'] ?? '';
        if (!in_array($layout, array('remark','reveal'))) {
            return false;
        }

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $arrSettings = $aeSettings->getPlugins('options', 'decktape');

        if ($arrSettings === array()) {
            return false;
        }

        $sScriptName = $arrSettings['script'];

        if (!$aeFiles->fileExists($sScriptName)) {
            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                $aeDebug->here('Decktape, file '.$sScriptName.' didn\'t exists', 5);
            }
            /*<!-- endbuild -->*/
            return false;
        }

        $aeTask = \MarkNotes\Tasks\Convert::getInstance();

        // Get the temporary name for the HTML and PDF files
        list($tmpHTML, $tmpPDF) = $aeTask->getTempNames($params);

        // Derive the resulting filename
        // will be .reveal.pdf or .remark.pdf
        $finalPDF = $aeTask->getFileName($params['filename'], $layout).'.pdf';

        // Just in case of the file was already created in the /tmp folder but, for one
        // or an another reason, not yet copied in the final folder.

        if ($aeFiles->fileExists($tmpPDF)) {

            // Remane the temporary with its final name
            // Note : the PDF file was perhaps already moved by the convert script
            $aeFiles->renameFile($tmpPDF, $finalPDF);
        } else {
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->loadPlugins('content', 'slides');
            $args = array(&$params);

            $params['layout'] = $layout;

			// true = stop on the first plugin which return "true" i.e. has done the job
            $aeEvents->trigger('export.slides', $args, true);

            $html = $params['html'];

            // And store that HTML to the disk
            file_put_contents($tmpHTML, $html);

            // Create a script on the disk
            // Phantomjs (used by the Decktape conversion) should be started from the folder where the
            // HTML file to convert stay so use the Windows PUSH instruction to change the default directory
            // the time needed to run the script
            // Be carefull : decktape don't like accentuated characters
            // names below (to the html and pdf file) shouldn't contains any accentuated charcters
            // Use 'chcp 65001' command, accentuated characters won't be correctly understand if
            // the file should be executable (like a .bat file)
            // see https://superuser.com/questions/269818/change-default-code-page-of-windows-console-to-utf-8
            $sProgram =
                '@ECHO OFF'.PHP_EOL.
                'chcp 65001'.PHP_EOL.
                'pushd "'.dirname($tmpHTML).'"'.PHP_EOL.
                '"'.$sScriptName.'" "'.dirname($sScriptName).DS.'decktape.js" '.$layout.' "'.basename($tmpHTML).'"'.
                ' "'.basename($tmpPDF).'"'.PHP_EOL.
                'popd';

            $slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($params['filename'])));

            $fScriptFile = dirname($tmpHTML).DS.$slug.'.bat';

            $aeFiles->fwriteANSI($fScriptFile, $sProgram);

            // Run the script. This part can be long depending on the number of slides in the HTML file to convert
            $output = array();

            exec($fScriptFile, $output);

            try {
                if ($aeFiles->fileExists($tmpPDF)) {
                    // Remane the temporary with its final name
                    // Note : the PDF file was perhaps already moved by the convert script
                    $aeFiles->renameFile($tmpPDF, $finalPDF);
                }
            } catch (Exception $e) {
                $finalPDF = '';
            }
        }

        $params['output'] = $finalPDF;

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.pdf', __CLASS__.'::doIt');
        return true;
    }
}
