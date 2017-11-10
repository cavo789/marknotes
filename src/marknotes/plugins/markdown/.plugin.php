<?php
/**
 * Definition of a plugin of type Markdown.
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

require_once (dirname(__DIR__).DS.'.plugin.php');

abstract class Plugin extends \MarkNotes\Plugins\Plugin
{
	/**
	 * The child class should implement the readMD() function
	 */
	abstract public static function readMD(array &$params = array()) : bool;

	/**
	 * Capture the markdown.read event and attach the readMD() function
	 */
	public function bind(string $plugin) : bool
	{
		if ($bReturn = $this->canRun()) {
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->bind('markdown.read', static::$me.'::readMD', $plugin);
		}

		return $bReturn;
	}
}
