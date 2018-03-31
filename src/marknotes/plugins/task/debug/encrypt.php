<?php
/**
 * Debuging - Encryption test
 * This simple script will call the encrypt task then will
 * unencrypt the data and will display the three info :
 *		1. Original string
 *		2. Then encrypted data
 *		3. The unencrypted data  (and 3 should be equal to 1)
 *
 * Answer to URL index.php?task=task.debug.encrypt
 */
namespace MarkNotes\Plugins\Task\debug;

defined('_MARKNOTES') or die('No direct access allowed');

class Encrypt extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.debug.encrypt';
	protected static $json_options = 'plugins.options.task.debug.encrypt';

	public static function run(&$params = null) : bool
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('task.encrypt.encrypt');

		// Define the answer
		$arr = array();

		$arr['original']='Marknotes is an amazing Open Source software';

		// -------------------------------------
		// Try the encryption
		$info = array('data'=>$arr['original']);
		$args=array(&$info);
		$aeEvents->trigger('task.encrypt.encrypt::encrypt', $args);
		$arr['encrypted'] = $args[0]['data'];

		// -------------------------------------
		// Try the unencryption
		$aeEvents->loadPlugins('task.encrypt.unencrypt');
		$info = array('data'=>$arr['encrypted']);
		$args=array(&$info);
		$aeEvents->trigger('task.encrypt.unencrypt::unencrypt', $args);
		$arr['decrypted'] = $args[0]['data'];

		// And return info
		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: application/json');
		echo json_encode($arr, JSON_PRETTY_PRINT);

		return true;
	}
}
