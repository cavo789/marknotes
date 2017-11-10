<?php
/**
 * Get interface buttons - Return the HTML with all enabled buttons
 */
namespace MarkNotes\Plugins\Task\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class GetButtons extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.buttons';
	protected static $json_options = '';

	/**
	 * Return the code for showing the login form and respond to the login action
	 */
	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Call plugins that are responsible to add icons to the toolbar
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('buttons');
		$buttons = array();
		$args = array(&$buttons);
		$aeEvents->trigger('buttons::add.buttons', $args);

		$buttons = $args[0];

		header('Content-Type: application/json');
		echo json_encode(array('status'=>1,'buttons'=>$buttons));

		return true;
	}
}
