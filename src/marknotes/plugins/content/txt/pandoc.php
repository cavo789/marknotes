<?php

namespace MarkNotes\Plugins\Content\TXT;

defined('_MARKNOTES') or die('No direct access allowed');

class Pandoc
{
    /**
     * Generate a TXT file thanks to Pandoc
     */
    public static function doIt(&$params = null)
    {
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // $sScriptName string Absolute filename to the pandoc.exe script
        $arrPandoc = $aeSettings->getPlugins('options', 'pandoc');

        if ($arrPandoc === array()) {
            return false;
        }

        $sScriptName = $arrPandoc['script'];

        if (!$aeFiles->fileExists($sScriptName)) {
            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                $aeDebug->here('Pandoc, file '.$sScriptName.' didn\'t exists', 5);
            }
            /*<!-- endbuild -->*/
            return false;
        }

        $aeTask = \MarkNotes\Tasks\Convert::getInstance();

        $final = $aeTask->getFileName($params['filename'], $params['task']);

		// Get a slug of the filename
		$slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($params['filename'])));

		// Display a .md file, call plugins and output note's content
		$filename = $aeSettings->getFolderDocs(true).$params['filename'];
		$aeMarkdown = \MarkNotes\FileType\Markdown::getInstance();
		$content = $aeMarkdown->read($filename);

		// ----------------------------------------
		//
		// Create a temporary version of the .md file, in the temporary folder
		$tmpMD = $aeSettings->getFolderTmp().$slug.'.md';
		$content=$aeMarkdown->read($filename);

		// Escape slashes
		$content=str_replace('\\', '\\\\', $content);

		// Note : pandoc use LaTeX which can incorrectly understand the "_" character.
		// So, escape it before converting to PDF
		$content=str_replace('_', '\_', $content);

        $aeFiles->createFile($tmpMD,$content);

        // $params['task'] is the output format (f.i. pdf), check if there are options to use
        // for that format
        $options = isset($arrPandoc['options'][$params['task']]) ? $arrPandoc['options'][$params['task']] : '';

        // Create a script on the disk
        // Use 'chcp 65001' command, accentuated characters won't be correctly understand if
        // the file should be executable (like a .bat file)
        // see https://superuser.com/questions/269818/change-default-code-page-of-windows-console-to-utf-8

        $debugFile = $aeSettings->getFolderTmp().$slug.'_debug.log';

		$sProgram =
			'@ECHO OFF'.PHP_EOL.
			'chcp 65001'.PHP_EOL.
			'"'. $sScriptName.'" '. $options . ' -o "'.$final.'" '.
			'"'.$tmpMD.'" > '.$debugFile.' 2>&1'.PHP_EOL.
			'copy "'.basename($final).'" "'.rtrim(dirname($final),DS).DS.'"'.PHP_EOL;

        $fScriptFile = $aeSettings->getFolderTmp().$slug.'.bat';

        $aeFiles->fwriteANSI($fScriptFile, $sProgram);

        // Run the script. This part can be long depending on the number of slides in the HTML file to convert
        $output = array();
        exec("start cmd /c ".$fScriptFile, $output);

        $params['output'] = $final;

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.txt', __CLASS__.'::doIt');
        return true;
    }
}
