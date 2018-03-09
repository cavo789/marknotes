<?php

/**
 * Download class.  Used f.i. by the MarkNotes\Tasks\pdf.php file in order to force the
 * download of the generated pdf file.
 */

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class Download
{
	protected static $hInstance = null;

	public function __construct()
	{
		return true;
	}

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Download();
		}

		return self::$hInstance;
	}

	/**
	 *	$type contains the output format (doc, pdf, ...)
	 */
	public function run(string $fname, string $type) : bool
	{
		$bReturn = false;

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$slug = $aeFunctions->slugify($aeFiles->removeExtension(basename($fname))).'.'.$type;

		if ($aeFiles->exists($fname)) {
			// And send the file to the browser
			switch ($type) {
				case 'doc':
				case 'docx':
					header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
					header('Content-Transfer-Encoding: binary');
					break;
				case 'pdf':
					header('Content-Type: application/pdf');
					header('Content-Transfer-Encoding: binary');
					break;
				case 'txt':
					header('Content-Type: text/plain');
					header('Content-Transfer-Encoding: ascii');
					break;
				case 'zip':
					header("Content-Description: File Transfer");
					header("Content-type: application/octet-stream");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					break;
			}

			header('Content-Disposition: attachment; filename="'.$slug.'"');
			header('Content-Length: '.filesize(utf8_decode($fname)));
			header('Accept-Ranges: bytes');
			header("Pragma: no-cache");
			header("Expires: 0");
			ob_end_flush();
			@readfile(utf8_decode($fname));

			$bReturn = true;
		} else { // if ($content!=='')
			/*<!-- build:debug -->*/
			$aeSettings	= \MarkNotes\Settings::getInstance();
			if ($aeSettings->getDebugMode()) {
				echo __FILE__."-".__LINE__." - The file ".$fname." doesn't exists<br/>";
			}
			/*<!-- endbuild -->*/

			$bReturn = false;
		}
		return $bReturn;
	}
}
