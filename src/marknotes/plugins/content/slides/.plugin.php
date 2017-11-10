<?php
/**
 * Definition of a plugin of type Content Slides.
 */
namespace MarkNotes\Plugins\Content\Slides;

defined('_MARKNOTES') or die('No direct access allowed');

require_once (dirname(dirname(__DIR__)).DS.'.plugin.php');

abstract class Plugin extends \MarkNotes\Plugins\Plugin
{
	/**
	 * The child class should implement the doIt() function
	 */
	abstract public static function doIt(array &$params = array()) : bool;

	/**
	 * Capture the run event and attach the doIt() function
	 */
	public function bind(string $plugin) : bool
	{
		if ($bReturn = $this->canRun()) {
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->bind('run', static::$me.'::doIt', $plugin);
		}

		return $bReturn;
	}
}
