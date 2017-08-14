<?php

namespace MarkNotes\Plugins\Content\DOCX;

defined('_MARKNOTES') or die('No direct access allowed');

class Pandoc
{
    /**
     *
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

		// Display a .md file, call plugins and output note's content
		$filename = $aeSettings->getFolderDocs(true).$params['filename'];
		$aeMarkdown = \MarkNotes\FileType\Markdown::getInstance();
		$content = $aeMarkdown->read($filename);

		// ----------------------------------------
		//
		// Create a temporary version of the .epub file, in the temporary folder
		$slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($params['filename'])));

		$tmpMD = $aeSettings->getFolderTmp().$slug.'.md';
		$aeMarkdown = \MarkNotes\FileType\Markdown::getInstance();

		$content=$aeMarkdown->read($filename);
		$aeFiles->createFile($tmpMD,$content);

        $aeTask = \MarkNotes\Tasks\Convert::getInstance();

        $final = $aeTask->getFileName($params['filename'], $params['task']);

        $slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($params['filename'])));

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
            '"'. $sScriptName.'" -s '. $options . ' -o "'.basename($final).'" '.
            '"'.$tmpMD.'" > '.$debugFile.' 2>&1'.PHP_EOL.
            'copy "'.basename($final).'" "'.rtrim(dirname($final),DS).DS.'"'.PHP_EOL;

        $fScriptFile = $aeSettings->getFolderTmp().$slug.'.bat';

        $aeFiles->fwriteANSI($fScriptFile, $sProgram);


        // Run the script. This part can be long depending on the number of slides in the HTML file to convert
        $output = array();
        exec("start cmd /c ".$fScriptFile, $output);

		if (!$aeFiles->fileExists($final)) {

			$msg = $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists');
			$msg = str_replace('%s', '<strong>'.$final.'</strong>', $msg);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->here('#DebugMode# - File '.$final.' not found', 10);
			}
			/*<!-- endbuild -->*/

			echo $msg.PHP_EOL.PHP_EOL;

			echo '<p>Check to start <strong>'.$fScriptFile.'</strong> manually; indeed, sometimes it doesn\'t work within PHP but well manually; with the user\'s OS credentials (PHP permissions problems). Then, just refresh this page.</p>';

		} // if (!$aeFiles->fileExists($final))

        $params['output'] = $final;

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.docx', __CLASS__.'::doIt');
        return true;
    }
}
