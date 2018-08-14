<?php
/**
 * Parse the markdown content and replace emoji's thanks to
 * the LitEmoji library
 * @link https://github.com/elvanto/litemoji
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class MindMap extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.mindmap';
	protected static $json_options = '';

	public static function readMD(array &$params = []) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$markdown = $params['markdown'];

		if (strpos($markdown, '%MINDMAP_START%') !== false) {
			$pattern = '/\%MINDMAP_START\%/';
			preg_match($pattern, $markdown, $matches, PREG_OFFSET_CAPTURE);

			// Get everything between the %MINDMAP_START% tag and
			// %MINDMAP_END%
			$startLen = strlen($matches[0][0]);
			$startPos = $matches[0][1];

			$pattern = '/\%MINDMAP_END\%/';
			preg_match($pattern, $markdown, $matches, PREG_OFFSET_CAPTURE);

			$endLen = strlen($matches[0][0]);
			$endPos = $matches[0][1];

			// Get %MINDMAP_START%(text)%MINDMAP_END%
			$original = substr($markdown, $startPos, $endPos - $startPos + $endLen);

			// Retrieve only the data
			$len = $endPos - $startPos;
			$items = substr($markdown, $startPos + $startLen, $endPos - $startPos - $startLen);

			$items = str_replace(["\r\n", "\n", "\r"], PHP_EOL, $items);
			//$items = str_replace("\t", '   ', $items);

			$maps = '';

			$arr = explode(PHP_EOL, $items);

			// Process line by line and remove the bullet character
			// (i.e. replace "* Item" by "Item")
			foreach ($arr as $line) {
				$line = str_replace(["\r\n", "\n", "\r"], '', $line);
				$line = preg_replace("/\t[\*\ \-]/", "\t", $line);
				$maps .= ltrim($line, '* -') . "\r\n";
			}
			$maps = '<pre style="display:none" class="MN_mindmap">' . $maps . '</pre>';

			$markdown = str_replace($original, $maps, $markdown);

			$params['markdown'] = $markdown;
		}

		return true;
	}

	/**
	 * Verify if the plugin is well needed and thus have a reason
	 * to be fired
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			$aeFiles = \MarkNotes\Files::getInstance();
			// Be sure the library is there
			$lib = __DIR__ . '/emoji/libs/litemoji/LitEmoji.php';
			$bCanRun = $aeFiles->exists($lib);
		}

		return $bCanRun;
	}
}
