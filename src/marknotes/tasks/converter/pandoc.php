<?php

namespace MarkNotes\Tasks\Converter;

defined('_MARKNOTES') or die('No direct access allowed');

class Pandoc
{
	protected static $hInstance = null;

	public function __construct()
	{
		return true;
	}

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Pandoc();
		}

		return self::$hInstance;
	}

	/**
	 * Convert a HTML to an another format (docx, pdf, ...) thanks to pandoc
	 */
	public static function run(array $params) : string
	{

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// $sScriptName string Absolute filename to the pandoc.exe script
		$arrPandoc = $aeSettings->getConvert('pandoc');
		$sScriptName= $arrPandoc['script'];

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

		$debugFile=$aeSettings->getFolderTmp().$slug.'_debug.log';

		$sProgram =
			'@ECHO OFF'.PHP_EOL.
			'chcp 65001'.PHP_EOL.
			'pushd "'.dirname($aeSettings->getFolderDocs(true).$params['filename']).'"'.PHP_EOL.
			'"'. $sScriptName.'" -s '. $options . ' -o "'.basename($final).'" '.
			'"'.basename($params['filename']).'" > '.$debugFile.' 2>&1'.PHP_EOL.
			'popd'.PHP_EOL;

		$fScriptFile = $aeSettings->getFolderTmp().$slug.'.bat';

		$aeFiles->fwriteANSI($fScriptFile, $sProgram);

		echo '<pre>'.$sProgram.'</pre>';

		// Run the script. This part can be long depending on the number of slides in the HTML file to convert
		$output = array();
		exec("start cmd /c ".$fScriptFile, $output);

		// $output is an array and contains the result of the script. If at least one line of the output start with
		// Error:, show the debug information and stop the code
		/*foreach ($output as $line) {
            if (substr($line, 0, 6) === 'Error:') {
                die("<pre style='background-color:orange;'>".__FILE__." - ".__LINE__."<br/>There is an error with the pandoc script<br/><br/>".print_r($output, true)."</pre>");
            }
        }*/

		return $final;
	}
}
