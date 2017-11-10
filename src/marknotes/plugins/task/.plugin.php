<?php
/**
 * Definition of a plugin of type Task.
 */
namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

require_once (dirname(__DIR__).DS.'.plugin.php');

abstract class Plugin extends \MarkNotes\Plugins\Plugin
{
	/**
	 * Run a task
	 */
	abstract public static function run(&$params = null) : bool;

	/**
	 * Capture the run event
	 */
	public function bind(string $plugin) : bool
	{
		if ($bReturn = $this->canRun()) {
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->bind('run', static::$me.'::run', $plugin);
		}

		return $bReturn;
	}
}
