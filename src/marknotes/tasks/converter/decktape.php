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
	public static function run(array $params) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// $sScriptName string Absolute filename to the phantomjs.exe script
		$arrDecktape = $aeSettings->getConvert('decktape');
		$sScriptName= $arrDecktape['script'];

		$aeTask = \MarkNotes\Tasks\Convert::getInstance();

		// Get the temporary name for the HTML and PDF files
		list($tmpHTML, $tmpPDF) = $aeTask->getTempNames($params);

		// Derive the resulting filename
		$finalPDF = $aeTask->getFileName($params['filename'], $params['task']);

		// Get the HTML version of the note
		$layout = $params['layout'];

		$aeTask = \MarkNotes\Tasks\Slideshow::getInstance();
		$html = $aeTask->run($params);

		// And store that HTML to the disk
		$aeFiles->rewrite($tmpHTML, $html);
/*<!-- build:debug -->*/
die("<h1>Died in ".__FILE__.", line ".__LINE__." : </h1>");
/*<!-- endbuild -->*/
		// Get the type of slideshow (reveal or remark)
		//$type = $aeSettings->getSlideshowType();

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
			'"'.$sScriptName.'" "'.dirname($sScriptName).DS.'decktape.js" '.$type.' "'.basename($tmpHTML).'"'.
			' "'.basename($tmpPDF).'"'.PHP_EOL.
			'popd';

		$slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($params['filename'])));

		$fScriptFile = dirname($tmpHTML).DS.$slug.'.bat';

		$aeFiles->rewrite($fScriptFile, $sProgram);

		// Run the script. This part can be long depending on the number of slides in the HTML file to convert
		$output = array();

		exec($fScriptFile, $output);

		// $output is an array and contains the result of the script. If at least one line of the output start with
		// Error:, show the debug information and stop the code
		/*foreach ($output as $line) {
            if (substr($line, 0, 6) === 'Error:') {
                die("<pre style='background-color:orange;'>".__FILE__." - ".__LINE__."<br/>There is an error with the deckTape script<br/><br/>".print_r($output, true)."</pre>");
            }
        }*/

		try {
			if ($aeFiles->exists($tmpPDF)) {
				// Remane the temporary with its final name
				// Note : the PDF file was perhaps already moved by the convert script
				$aeFiles->rename($tmpPDF, $finalPDF);
			}
		} catch (Exception $e) {
			$finalPDF = '';
		}

		return $finalPDF;
	}
}
