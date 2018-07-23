<?php
/**
 * Get the list of tags and start search for each of them
 * So "prefill" the cache with the content of these tags
 *
 * Answer to index.php?task=task.search.prefill
 */
namespace MarkNotes\Plugins\Task\Search;

defined('_MARKNOTES') or die('No direct access allowed');

class Prefill extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.search';
	protected static $json_options = 'plugins.options.task.search';

	/**
	 * Get the content of the cache
	 * @return string
	 */
	private static function doit() : array
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the current URL and points to the search.php script
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$search = $aeFunctions->getCurrentURL().'search.php';

		// Get tags.json if the file exists
		$fname = $aeSettings->getFolderWebRoot().'tags.json';
		if ($aeFiles->exists($fname)) {
			$content = json_decode($aeFiles->getContent($fname), true);
			foreach ($content as $tag) {
				if($tag!=='') {
					$url = $search.'?str='.utf8_decode($tag);
					$arr[$tag]=$url;
				} // if($tag!=='')
			}
		}
		return $arr;
	}

 	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arr = self::doit();

		header('Content-Type: text/html');

		$html = file_get_contents(__DIR__.'/assets/prefill.frm');

		// Get the web root like http://localhost/notes/
		$sRoot = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$html = str_replace('%ROOT%', $sRoot, $html);

		$content = '';

		foreach ($arr as $tag=>$url) {
			$content .= '<li data-url="'.$url.'">'.$tag.'</li>';
		}

		$content = '<ol id="tags">'.$content.'</ol>';

		$html = str_replace('%CONTENT%', $content, $html);

		echo $html;

		return true;
	}
}
