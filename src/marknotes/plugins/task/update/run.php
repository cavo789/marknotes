<?php
/**
 * Update marknotes
 */
namespace MarkNotes\Plugins\Task\Update;

defined('_MARKNOTES') or die('No direct access allowed');

class Update extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.update';
	protected static $json_options = 'plugins.options.task.update';

	public static function run(&$params = null) : bool
	{

/*<!-- build:debug -->*/
$aeDebug = \MarkNotes\Debug::getInstance();
$aeDebug->here("", 10);
/*<!-- endbuild -->*/
		return true;
	}
}
