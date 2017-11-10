<?php

/*
 * Export a note to a .pdf, thanks to dompdf
 *
 * dompdf requires external libraries : https://github.com/dompdf/dompdf/wiki/Requirements
 *   - php-font-lib (https://github.com/PhenX/php-font-lib.git) and
 *   - php-svg-lib (https://github.com/PhenX/php-svg-lib.git)
 * They should be installed manually
 */

namespace MarkNotes\Plugins\Content\PDF;

defined('_MARKNOTES') or die('No direct access allowed');

class DomPDF
{

    private static $layout = 'pdf';

	/**
     * Make the conversion
     */
    public static function doIt(&$params = null)
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

		// ----------------------------------------
		// Call the generic class for file conversion
        $aeConvert = \MarkNotes\Tasks\Convert::getInstance($params['filename'], static::$layout, 'dompdf');

		// Get the filename, once exported (f.i. notes.pdf)
		$final = $aeConvert->getFileName();

		// Derive the filename for the debugging file
		$debugFile = $aeSettings->getFolderTmp().$aeConvert->getDebugFileName().'.html';

        // Use the pdf template and not the "html" one
        $params['task'] = 'pdf';
        $params['template'] = 'pdf';

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('Get the note\'s html content', 'debug');
		}
		/*<!-- endbuild -->*/

		$aeTask = \MarkNotes\Tasks\Display::getInstance();
		$html = $aeTask->run($params);

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log("Initialize Dompdf", "debug");
			$aeDebug->log("**Dompdf error logfile** : ".$debugFile, "debug");
		}
		/*<!-- endbuild -->*/

		include_once $aeSettings->getFolderLibs().'dompdf/dompdf/autoload.inc.php';

		$dompdf_options = array(
		    'logOutputFile' => $debugFile,
		    'isHtml5ParserEnabled' => true,
		    'debugPng' => false,
		    'debugKeepTemp' => false,
		    'debugCss' => false,
		    'debugLayout' => false,
		    'debugLayoutLines' => false,
		    'debugLayoutBlocks' => false,
		    'debugLayoutInline' => false,
		    'debugLayoutPaddingBox' => false
		);

		$dompdf = new \Dompdf\Dompdf($dompdf_options);

		$dompdf->getOptions()->setTempDir($aeSettings->getFolderTmp());

		// Needed for f.i. correctly retrieve images (relative filenames)
		$dompdf->set_base_path(dirname($final).DS);

		// instantiate and use the dompdf class
		$dompdf->loadHtml($html);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'portrait');

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('Start Dompdf rendering', 'debug');
		}
		/*<!-- endbuild -->*/

		// Render the HTML as PDF
		// *** Don't stop on errors ***
		@$dompdf->render();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('Save Dompdf output to '.$final, 'debug');
		}
		/*<!-- endbuild -->*/

		$output = $dompdf->output();

		file_put_contents($final, $output);

        $params['output'] = $final;

        return true;

    }

    /**
     * Attach the function and responds to events
     */
    public function bind(string $plugin)
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.pdf', __CLASS__.'::doIt', $plugin);
        return true;
    }
}
