<?php
/**
 * ELF - File manager
 * Initialize variables that will be used by the connector ELF file
 */
namespace MarkNotes\Plugins\Task\Elf;

defined('_MARKNOTES') or define('_MARKNOTES', 1);
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

class Initialize
{
	/**
	* Return the current URL
	*
	* @return type string
	*/
	private static function getCurrentURL() : string
	{

		$ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
		$protocol = 'http';
		// SERVER_PROTOCOL isn't set when the script is fired through a php-cli
		if (isset($_SERVER['SERVER_PROTOCOL'])) {
			$spt = strtolower($_SERVER['SERVER_PROTOCOL']);
			$protocol = substr($spt, 0, strpos($spt, '/')) . (($ssl)?'s':'');
		}

		$port = '80';
		// SERVER_PORT isn't set when the script is fired through a php-cli
		if (isset($_SERVER['SERVER_PORT'])) {
			$port = $_SERVER['SERVER_PORT'];
			$port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':'.$port;
		}

		$host =
		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');

		$host = isset($host) ? rtrim(str_replace(DS, '/', $host), '/') : $_SERVER['SERVER_NAME'].$port;

		$self= $_SERVER['PHP_SELF'];
		$self=dirname(dirname(dirname($self)));
		$self=dirname(dirname(dirname($self)));

		$return = $protocol.'://'.$host.rtrim(dirname($self),'/').'/';

		return $return;
	}

	public static function getSettings() : array
	{
		// Retrieve the root folder based on this script
		// Make a lot of dirname() since this current script
		// is deep in the marknotes folder's structure

		$webRoot=rtrim(dirname($_SERVER['SCRIPT_FILENAME']), DS);
		$webRoot=dirname(dirname(dirname($webRoot)));
		$webRoot=dirname(dirname(dirname($webRoot)));

		// Get it. A folder like "C:\site\marknotes\"
		$webRoot=str_replace('/', DS, $webRoot).DS;

		// Get the default docs folder ("C:\site\marknotes\docs\")
		$docs = 'docs'.DS;
		$title = '';
		$locale = 'en_us';
		$debug = 0;

		// Get from settings.json.dist then settings.json
		$arr = array('settings.json.dist', 'settings.json');

		foreach ($arr as $file) {
			if (file_exists($json = $webRoot.$file)) {
				try {
					$settings=json_decode(file_get_contents($json), true);

					if (isset($settings['site_name'])) {
						$title = $settings['site_name'];
					}

					if (isset($settings['debug'])) {
						if (isset($settings['debug']['enabled'])) {
							$debug = boolval($settings['debug']['enabled']);
						}
					}

					if (isset($settings['regional'])) {
						if (isset($settings['regional']['locale'])) {
							$locale = $settings['regional']['locale'];
						}
					}

					if (isset($settings['folder'])) {
						if (is_dir($webRoot.$settings['folder'].DS)) {
							$docs = $settings['folder'].DS;
						}
					}
				} catch (\Exception $e) {
				}
			}
		} // foreach ($arr as $file)

		// Get the URL to the /docs folder
		$url  = self::getCurrentURL().str_replace(DS, '/', $docs);

		// Include Marknotes classes
		// So we can use the Session classes
		$dir = dirname(dirname(dirname(dirname(__DIR__))));
		include_once $dir.'/marknotes/includes/debug_show_errors.php';

		// Load classes and initialize Marknotes
		include_once $dir.'/marknotes/includes/initialize.php';
		$class = new \MarkNotes\Includes\Initialize();
		$class->init($webRoot);
		unset($class);

		$aeEvents = \MarkNotes\Events::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Call the ACLs plugin
		$aeEvents->loadPlugins('task.acls.load');

		$args=array();

		$aeEvents->trigger('task.acls.load::run', $args);

		// And retrieve the list of protected folders
		$aeSession = \MarkNotes\Session::getInstance();
		$acls = $aeSession->get('acls');

		// Get the current logged username
		$username = trim($_SERVER['PHP_AUTH_USER'] ?? '');
		if ($username == '') {
			$username = trim($_SERVER['REMOTE_USER'] ?? '');
		}

		// Finally return informations to ELF
		$arr = array(
			'root'=>$webRoot,
			'docs'=>rtrim($webRoot,DS).DS.$docs,
			'url'=>$url,
			'title'=>$title,
			'locale'=>$locale,
			'debug'=>$debug,
			'username'=>$username,
			'acls'=>json_encode($acls)
		);

		return $arr;
	}

	// Initialize is not a task that we can call through to events
	// manager of marknotes. So, if the bind() function is called,
	// this means that the "index.php?task=task.elf.initialize" has
	// been accessed on the URL and it's not normal
	public function bind(string $plugin) : bool
	{
		die('No direct access allowed to this file');
		return false;
	}
}
