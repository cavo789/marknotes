<?php
/**
 * ELF - File manager
 * Show the ELF interface in a iframe
 *
 * Answer to URL like index.php?task=task.elf.show
 *
 * @link https://github.com/Studio-42/elFinder
 */
namespace MarkNotes\Plugins\Task\Elf;

defined('_MARKNOTES') or die('No direct access allowed');

class Show extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.filemanager';
	protected static $json_options = 'plugins.options.task.filemanager';

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$app = $aeSettings->getFolderAppRoot();

		// ELF is a file-manager and the interface is based
		// on a file called "elfinder.html".
		// Build the URL to the file so we can then use it
		// in a iframe
		$dir = dirname(str_replace($app, '', __FILE__));
		$dir = rtrim(str_replace(DS, '/', $dir), DS).'/';
		$dir .= 'libs/elf/';

		$url = $aeFunctions->getCurrentURL().$dir;

		$html = '<iframe id="idELF" src="'.$url.'elfinder.html" '.
			'width="100" height="500">'.
  			'<p>Your browser does not support iframes.</p>'.
			'</iframe>'.
			'<script>'.
			'	document.title = "blah";'.
			'	document.getElementById("idELF").width = (window.innerWidth -30)+"px";'.
			'	document.getElementById("idELF").height = (window.innerHeight -30)+"px";'.
    		'</script>';

		echo $html;

		die();
	}
}
