<?php

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class Convert
{
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new Convert();
        }

        return self::$_Instance;
    }

    /**
     * Taking the name of the note, provide the name of the .pdf to create
     * F.i. for c:\sites\marknotes\docs\so nice app.md returns  c:\sites\marknotes\docs\so_nice_app.pdf
     */
    public function getFileName(string $fname, string $extension = 'pdf') : string
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // $fname should be an absolute filename; not a relative one
        if (strpos($fname, $aeSettings->getFolderDocs(true)) === false) {
            $fname = $aeSettings->getFolderDocs(true).ltrim($fname, DS);
        }

        $fname = $aeFiles->replaceExtension(
            str_replace('/', DS, $fname),
            $extension
        );

        return $fname;
    }

    /**
     * Ask for a folder name (f.i. c:\sites\marknotes\docs\) and return two temporary filenames; one with
     * the .html extension and the second with .pdf
     */
    public function getTempNames(array $params) : array
    {
        $tmpHTML = '';
        $tmpOutput = '';

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $tmp = $aeSettings->getFolderTmp();
        $slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($params['filename'])));

        $tmpHTML = $tmp.$slug.'.html';

        // If present, layout can be 'reveal' or 'remark'
        $layout = isset($params['layout']) ? '.' . $params['layout'] : '';

        // task contains the extension (docx, pdf, ...)
        $tmpOutput = $tmp.$slug.$layout.'.'.$params['task'];

        return array($tmpHTML, $tmpOutput);
    }

    public function run(array $params)
    {
        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3) != '.md') {
            $params['filename'] .= '.md';
        }

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $layout = isset($params['layout']) ? $params['layout'] : '';

        // Retrieve the fullname of the file that will be generated
        // The task can be "docx" or "pdf" i.e. the file's extension
        $final = self::getFileName($params['filename'], $params['task']);

        // And check if the file already exists => faster than creating on-the-fly
        if ($aeFiles->fileExists($final)) {
            $fMD = $aeSettings->getFolderDocs(true).$aeFiles->replaceExtension($params['filename'], 'md');
            if (filemtime($final) < filemtime($fMD)) {
                // The note has been modified after the generation of the .pdf => no more up-to-date
                $final = '';
            }
        }

        // Doesn't exists yet ? Create it
        if (($final === '') || (!$aeFiles->fileExists($final))) {

            // Try to use the best Converter
            $converter = '';

            // The exec() function should be enabled to use deckTape
            $aeFunctions = \MarkNotes\Functions::getInstance();
            if (!$aeFunctions->ifDisabled('exec')) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    if (in_array($layout, array('reveal', 'remark'))) {

                        // deckTape is only for slideshow view and not for HTML view
                        $converter = ($aeSettings->getConvert('decktape') !== array() ? 'decktape' : '');
                    } else { // if (in_array($layout, array('reveal', 'remark')))

                        // Check for pandoc
                        $converter = ($aeSettings->getConvert('pandoc') !== array() ? 'pandoc' : '');
                    }
                } // if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            } // if (!$aeFunctions->ifDisabled('exec'))

            switch ($converter) {
                case 'decktape':
                    $aeConvert = \MarkNotes\Tasks\Converter\Decktape::getInstance();
                    break;

                case 'pandoc':
                    $aeConvert = \MarkNotes\Tasks\Converter\Pandoc::getInstance();
                    break;

                default:
                    $aeConvert = \MarkNotes\Tasks\Converter\Dompdf::getInstance();
                    break;
            }

            $final = $aeConvert->run($params);
        }

        // Return the fullname of the file
        return $final;
    }
}
