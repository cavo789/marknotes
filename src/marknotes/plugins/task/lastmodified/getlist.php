<?php
/**
 * Last_modified - Retrieve the list of last mod notes;
 * return a JSON string
 *
 * Answer to URL like index.php?task=task.lastmodified.getlist
 */
namespace MarkNotes\Plugins\Task\LastModified;

defined('_MARKNOTES') or die('No direct access allowed');

class GetList extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.lastmodified';
	protected static $json_options = 'plugins.options.task.lastmodified';

	/**
	 * Get the list of the last xxx (f.i. 10) modified nodes
	 * Return an array with the filename and the md5
	 */
	private static function getFiles() : array
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the docs folder
		$doc = $aeSettings->getFolderDocs(false);

		// Run the filter_list task to remove any protected
		// files not allowed for the current user

		$aeEvents->loadPlugins('task.listfiles.get');
		$args=array(&$arrFiles);
		$aeEvents->trigger('task.listfiles.get::run', $args);

		// Retrieve the filtered array i.e. that Files
		// well accessible to the current user
		$arrFiles=$args[0];

		// Sort on the key i.e. on the timestamp since
		// $arrFiles contains things like this :
		//
		// 	[1520937247] => docs/folder/file.md
		//	[1520935082] => docs/folder/file999.md
		//  [1520874329] => docs/folder2/file2..md
		//
		// The index used is the timestamp of the file so
		// making a revert sort will give us the last modified files

		krsort($arrFiles);

		// Retrieve the number of notes to displayed in the lastmod
		// list
		$count = self::getOptions('items_count', 10);

		$arr = array();

		$i = 0;
		foreach ($arrFiles as $timestamp => $file) {
			$i+=1;

			$file = $aeFiles->removeExtension($file);
			$file = str_replace('/', DS, $file);

			if ($aeFunctions->startsWith($file, $doc)) {
				// Remove "docs/" to have filename more readable
				$file = substr($file, strlen($doc));
			}

			$arr[] = array(
				'file'=>$file,
				'id'=>md5($doc.$file) // md5 needs "docs/"
			);

			if ($i >= $count) {
				break;
			}
		} // foreach

		return $arr;
	}

	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrOptions = self::getOptions('list', array());

		// Sort the array
		sort($arrOptions);

		$arr = array();

		$arr['title'] = $aeSettings->getText('lastmodified_title', 'Notes modified recently');
		$arr['files'] = self::getFiles();

		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: application/json');
		echo json_encode($arr, JSON_PRETTY_PRINT);
		die();
	}
}
