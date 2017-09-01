<?php

/*
 * Export a note to a .pdf, thanks to pandoc
 */

namespace MarkNotes\Plugins\Content\PDF;

defined('_MARKNOTES') or die('No direct access allowed');

class Pandoc
{
    private static $layout = 'pdf';

	/**
     * Make the conversion
     */
    public static function doIt(&$params = null)
    {

		$bReturn = true;

        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		// ----------------------------------------
		// Call the generic class for file conversion
        $aeConvert = \MarkNotes\Tasks\Convert::getInstance($params['filename'], static::$layout, 'pandoc');

		// Get the filename, once exported (f.i. notes.txt)
		$final = $aeConvert->getFileName();

		// Check if pandoc is installed; if not, check if the exported file already exists
		if (!$aeConvert->isValid()) {

			if (!$aeFiles->fileExists($final)) {

				// No, doesn't exists

		        $bReturn = false;

			}

		} else { // if (!$aeConvert->isValid())

	        $arrPandoc = $aeSettings->getPlugins('options', 'pandoc');

			// Read the content of the .md file
			$filename = $aeSettings->getFolderDocs(true).$params['filename'];

			// Derive filenames

			$slug = $aeConvert->getSlugName();
			$debugFile = $aeConvert->getDebugFileName();

			// Get a copy of the .md note (in /temp folder), run any plugins
			$tempMD = $aeConvert->createTempNote();

			$sScript = $aeConvert->getScript($tempMD, $final);

			$aeConvert->Run($sScript, $final);

		} // if (!$aeConvert->isValid())

		if ($bReturn) $params['output'] = $final;

        return $bReturn;

    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.'.static::$layout, __CLASS__.'::doIt');
        return true;
    }
}




/*
namespace MarkNotes\Plugins\Content\PDF;

defined('_MARKNOTES') or die('No direct access allowed');

class Pandoc
{
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
            if ($aeSettings->getDebugMode()) {
                $aeDebug->here('Pandoc, file '.$sScriptName.' didn\'t exists', 5);
            }
            return false;
        }

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

		// ----------------------------------------
		//

        $aeTask = \MarkNotes\Tasks\Convert::getInstance();

        $final = $aeTask->getFileName($params['filename'], $params['task']);

        // $params['task'] is the output format (f.i. pdf), check if there are options to use
        // for that format
        $options = isset($arrPandoc['options'][$params['task']]) ? $arrPandoc['options'][$params['task']] : '';

        // Create a script on the disk
        // Use 'chcp 65001' command, accentuated characters won't be correctly understand if
        // the file should be executable (like a .bat file)
        // see https://superuser.com/questions/269818/change-default-code-page-of-windows-console-to-utf-8

        $debugFile = $aeSettings->getFolderTmp().$slug.'_debug.log';

		if ($aeFiles->fileExists($debugFile)) unlink($debugFile);

        $sProgram =
            '@ECHO OFF'.PHP_EOL.
            'chcp 65001'.PHP_EOL.
			'cd "'.$aeSettings->getFolderTmp().'"'.PHP_EOL.
            '"'. $sScriptName.'" -s '. $options . ' -o "'.basename($final).'" '.
            '"'.$tmpMD.'" > '.$debugFile.' 2>&1'.PHP_EOL.
            'copy "'.basename($final).'" "'.rtrim(dirname($final),DS).DS.'"'.PHP_EOL;

        $fScriptFile = $aeSettings->getFolderTmp().$slug.'.bat';

		$aeFiles->createFile($fScriptFile, $sProgram);
        //$aeFiles->fwriteANSI($fScriptFile, $sProgram);

		// ----------------------------------------
		//
		// Run the script

        // No timeout
        set_time_limit(0);

        // Run the script
        $output = array();

        exec("start cmd /c ".$fScriptFile, $output);

		if (!$aeFiles->fileExists($final)) {

			$msg = $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists');
			$msg = str_replace('%s', '<strong>'.$final.'</strong>', $msg);

			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->here('#DebugMode# - File '.$final.' not found', 10);
			}

			echo $msg.PHP_EOL.PHP_EOL;

			echo '<p>Check to start <strong>'.$fScriptFile.'</strong> manually; indeed, sometimes it doesn\'t work within PHP but well manually; with the user\'s OS credentials (PHP permissions problems). Then, just refresh this page.</p>';


			if ($aeSettings->getDebugMode()) {
				if ($aeFiles->fileExists($debugFile)) {
					$content = file_get_contents ($debugFile);
					echo '<h3>Content of the debug file : '.$debugFile.'</h3>';
					echo "<pre style='background-color:yellow;'>".$content."</pre>";
				}
			}


			die();

		} // if (!$aeFiles->fileExists($final))

        $params['output'] = $final;
        $params['stop_processing'] = true;

        return true;
    }

    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.pdf', __CLASS__.'::doIt');
        return true;
    }
}
*/
