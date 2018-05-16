<?php
/**
 * Allow to insert a .csv file in the note; convert the
 * .csv into an array
 *
 * For instance :
 *
 *	%CSV file.csv%
 *
 * Inspired by https://github.com/mre/CSVTable/blob/master/CSVTable.php
 */
namespace MarkNotes\Plugins\Content\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class CSV extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.csv';
	protected static $json_options = 'plugins.options.markdown.csv';

	static $delim = ';';
	static $enclosure = '"';
	static $md_sep = '|';
	static $length = 0;
	static $col_widths = 0;

	/**
	* Convert the CSV into a PHP array
	*/
	private static function toArray($csv) : array {
		$parsed = str_getcsv($csv, "\n"); // Parse the rows
		$output = array();
		foreach($parsed as &$row) {
			$row = str_getcsv($row, static::$delim, static::$enclosure); // Parse the items in rows
			array_push($output, $row);
		}
		return $output;
	}

	private static function createHeader($header_array) : string
	{
		return self::createRow($header_array).self::createSeparator();
	}

	private static function createSeparator() : string
	{
		$output = static::$md_sep;
		for ($i = 0; $i < self::$length - 1; ++$i) {
			$output .= str_repeat("-", self::$col_widths[$i]);
			$output .= " ".static::$md_sep;
		}
		$last_index = self::$length - 1;
		$output .= str_repeat("-", self::$col_widths[$last_index]);
		$output .= " ".static::$md_sep;
		return $output . "\n";
	}

	private static function createRows(array $rows) : string
	{
		$output = "";
		foreach ($rows as $row) {
			$output .= self::createRow($row);
		}
		return $output;
	}

	/**
	* Add padding to a string
	*/
	private static function padded(string $str, int $width) : string
	{
		if ($width < strlen($str)) {
			return $str;
		}
		$padding_length = $width - strlen($str);
		$padding = str_repeat(" ", $padding_length);
		return $str . $padding;
	}

	protected static function createRow(array $row) : string
	{
		$output = static::$md_sep;

		// Only create as many columns as the minimal number of elements
		// in all rows. Otherwise this would not be a valid Markdown table
		for ($i = 0; $i < self::$length; ++$i) {
			$element = self::padded($row[$i], self::$col_widths[$i]);
			$output .= $element;
			$output .= " ".static::$md_sep;
		}

		$output .= "\n"; // row ends with a newline

		return $output;
	}

	private static function minRowLength(array $arr) : int
	{
		$min = PHP_INT_MAX;
		foreach ($arr as $row) {
			$row_length = count($row);
			if ($row_length < $min) {
				$min = $row_length;
			}
		}
		return $min;
	}

	/*
	* Calculate the maximum width of each column in characters
	*/
	private static function maxColumnWidths(array $arr) : array
	{
		// Set all column widths to zero.
		$column_widths = array_fill(0, self::$length, 0);
		foreach ($arr as $row) {
			foreach ($row as $k => $v) {
				if ($column_widths[$k] < strlen($v)) {
					$column_widths[$k] = strlen($v);
				}
				if ($k == self::$length - 1) {
					// We don't need to look any further since
					// these elements will be dropped anyway
					// because all table rows must have the same
					// length to create a valid Markdown table.
					break;
				}
			}
		}
		return $column_widths;
	}

	private static function getMarkup(string $CSV) : string
	{
		// read options from settings.json
		static::$delim = self::getOptions('separator',';');
		static::$enclosure = self::getOptions('value_separator',';');
		static::$md_sep = self::getOptions('md_column_separator',';');

		$arrCSV = self::toArray($CSV);
		self::$length = self::minRowLength($arrCSV);
		self::$col_widths = self::maxColumnWidths($arrCSV);
		$arrHeader = array_shift($arrCSV);
		$header = self::createHeader($arrHeader);

		$rows = self::createRows($arrCSV);

		$markdown = $header. self::createRows($arrCSV);

		return $markdown;
	}

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($markdown = $params['markdown']) === '') {
			return true;
		}

		$aeDebug = \MarkNotes\Debug::getInstance();

		if (!(preg_match('/^%CSV ([^{\\n]*)%/m', $markdown, $match))) {
			// No CSV found; return
			return true;
		}

		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// Remember this filename i.e. the "master" file.
		$filename = $aeSettings->getFolderDocs(true).$aeSession->get('filename');

		// Retrieve every occurences of %CSV filename%
		if (preg_match_all('/^%CSV ([^{\\n]*)%/m', $markdown, $matches)) {

			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();

			// Retrieve the note fullpath.
			$root = dirname($filename).DS;

			// Loop and process every %CSV ..% tags
			for ($i=0; $i<count($matches[0]); $i++) {
				// $tag	=> $matches[0][0] will be f.i.
				//					"  %CSV file.csv%"
				// $file	=> $matches[2][0] will be f.i.
				//					"file.csv"
				list($tag, $file) = $matches;

				// %CSV file.csv% => when no path has been
				// modified, it means that the file is in the same
				// folder of the processed note i.e. $root
				$file[$i]=str_replace('/', DS, $file[$i]);

				if (!$aeFunctions->startsWith($file[$i], $root)) {
					$file[$i]=$root.$file[$i];
				}

				// Get the filename to include
				//$sFile = realpath(str_replace('/', DS, $file[$i]));
				/*<!-- build:debug -->*/

				$sFile = str_replace('/', DS, $file[$i]);

				if ($sFile=='') {
					// The file doensn't exists
					continue;
				}

				if ($aeFiles->exists($sFile)) {

					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug->log('reading '.$sFile, 'debug');
					}
					/*<!-- endbuild -->*/

					// Read the file
					$sCSVContent = trim($aeFiles->getContent($sFile));

					// A non breaking space is U+00A0 (Unicode)
					// but encoded as C2A0 in UTF-8
					// Replace by a space character
					$sCSVContent=preg_replace('/\x{00a0}/siu', ' ', $sCSVContent);

					$sCSV2MD = self::getMarkup($sCSVContent);
				} else {

					$sCSV2MD = '';

					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug->log('	Failure : file ['.$sFile.'] '.
							'not found !', 'error');

						$sCSV2MD = '<span class="devmode">'.
							DEV_MODE_PREFIX.' Failure : '.
						 	'file ['.$sFile.'] not found!</span>';
					}
					/*<!-- endbuild -->*/

					$sCSVContent = '';
				} // if ($aeFiles->exists($sFile)) {

				$markdown = str_replace($tag[$i], $sCSV2MD,
					$markdown);
			} // for ($i=0; $i<count($matches[0]); $i++) {

			$params['markdown'] = $markdown;
		}

		return true;
	}

}
