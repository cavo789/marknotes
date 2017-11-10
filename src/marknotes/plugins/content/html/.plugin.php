<?php
/**
 * Definition of a plugin of type Content HTML.
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

require_once (dirname(dirname(__DIR__)).DS.'.plugin.php');

abstract class Plugin extends \MarkNotes\Plugins\Plugin
{
	/**
	 * The child class should implement the doIt() function
	 */
	abstract public static function doIt(&$content = null) : bool;

	/**
	 * Capture the render.content event and attach the doIt() function
	 */
	public function bind(string $plugin) : bool
	{
		if ($bReturn = $this->canRun()) {
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->bind('render.content', static::$me.'::doIt', $plugin);
		}

		return $bReturn;
	}
}
