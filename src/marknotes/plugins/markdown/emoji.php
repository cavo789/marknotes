<?php
/**
 * Parse the markdown content and replace emoji's thanks to the LitEmoji library
 * @link https://github.com/elvanto/litemoji
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Emoji extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.emoji';
	protected static $json_options = '';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		require_once(__DIR__.'/emoji/libs/litemoji/LitEmoji.php');
		$params['markdown'] = \LitEmoji\LitEmoji::encodeUnicode($params['markdown']);

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
			// Be sure the library is there
			$bCanRun = is_file($lib=__DIR__.'/emoji/libs/litemoji/LitEmoji.php');
		}

		return $bCanRun;
	}
}
