<?php
/**
* What are the actions to fired when MarkNotes is running the "html" task ?
*/

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class HTML
{
	public static function run(&$params = null)
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		// Display the HTML rendering of a note
		$aeTask = \MarkNotes\Tasks\Display::getInstance();
		header('Content-Type: text/html; charset=utf-8');

		// Just run the HTML task and let any html plugins to be fired, in the order
		// defined in settings.json

		$html = $aeTask->run($params);

		echo $html;

		return true;
	}

	/**
	* Attach the function and responds to events
	*/
	public function bind(string $task)
	{
		/*<!-- build:debug -->*/
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeDebug->here("TOUJOURS UTILISÉ ???",1);
		/*<!-- build:debug -->*/
		die("<h1>Died in ".__FILE__.", line ".__LINE__." : </h1>");
		/*<!-- endbuild -->*/
		/*<!-- endbuild -->*/
		/*<!-- build:debug -->*/
		die("<h1>Died in ".__FILE__.", line ".__LINE__." : </h1>");
		/*<!-- endbuild -->*/
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->bind('run.task', __CLASS__.'::run', $task);
		return true;
	}
}
