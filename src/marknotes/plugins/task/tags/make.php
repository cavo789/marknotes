<?php
/**
 * Scan every documents under /docs and make an index i.e. for each
 * word, retrieve the number of times that this word appears
 */
namespace MarkNotes\Plugins\Task\Tags;

defined('_MARKNOTES') or die('No direct access allowed');

class Make extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.tags';
	protected static $json_options = 'plugins.options.task.tags';

	/**
	 * Retrieve the list of files but only when the user has access
	 * to it. Rely on the ACLs plugin for this purpose.
	 */
	private static function getFiles() : array
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();

		// Call the listfiles.get event and initialize $arrFiles
		$aeEvents = \MarkNotes\Events::getInstance();
		$args=array(&$arrFiles);
		$aeEvents->loadPlugins('task.listfiles.get');
		$aeEvents->trigger('task.listfiles.get::run', $args);
		$arrFiles = $args[0];

		return $arrFiles;
	}

	private static function makeIndex(string $content, array $index_frequency)
	{
		// Replace a few characters in the content, for instance a ":" is not a part
		// of a word; don't need it in our index
		$content = preg_replace("([|\*,;:\	\-\+\€\@\.\(\)\$\=\#\«\»\`\´\’\^\®\!\?\\\/\{\}\'\"\>\<\%\[\]])", " ", $content);
		$content = preg_replace("(\s{2,})", " ", $content);

		// Convert the string into an array
		$index = explode(" ", $content);

		// The word be at least ... characters length
		// Don't take in the index small words of 1, 2, 3, ... letters
		$minChars = self::getOptions('min_chars', '6');

		// And loop, take each word and count the number of times the word is found
		foreach ($index as $key => $value) {
			if (strlen($value) >= $minChars) {
				if (array_key_exists($value, $index_frequency)) {
					$index_frequency[$value] = $index_frequency[$value] + 1;
				} else {
					$index_frequency[$value] = 1;
				}
			}
		}

		return $index_frequency;
	}

	public static function run(&$params = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the list of files
		$arrFiles = self::getFiles();

		$arrIndex = array();

		// Process each files
		foreach ($arrFiles as $file) {
			$content = str_replace("\n", " ", file_get_contents(utf8_decode($file)));
			$arrIndex = self::makeIndex($content, $arrIndex);
		}

		$arrIndex = array_map("strtolower", $arrIndex);

		// Sort descending, the word the most used will be at the first
		// position in the array
		arsort($arrIndex);

		$minOccurences = self::getOptions('min_occurences', '10');
		$arrTags = array();
		foreach ($arrIndex as $word => $arrIndex) {
			if ($arrIndex < $minOccurences) {
				break;
			}
			$arrTags[] = utf8_encode($word);
		}

		sort($arrTags);

		$aeJSON = \MarkNotes\JSON::getInstance();
		$sReturn = $aeJSON->json_encode($arrTags);

		$aeSettings = \MarkNotes\Settings::getInstance();
		$fname = $aeSettings->getFolderWebRoot().'tags.json';

		$fTags = fopen($fname, "w") or die("Unable to create ".$fname."!");
		fwrite($fTags, json_encode($arrTags, JSON_PRETTY_PRINT));
		fclose($fTags);

		header('Content-Type: application/json; charset=UTF-8');
		header("cache-control: must-revalidate");
		$offset = 48 * 60 * 60;  // 48 hours
		$expire = "expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
		header($expire);
		echo '{"status":1,"message":"'.basename($fname).' created"}';
		return true;
	}
}
