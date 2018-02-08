<?php
/**
 * ELF - File manager
 * Initialize variables that will be used by the connector ELF file
 */
namespace MarkNotes\Plugins\Task\Elf;

//defined('_MARKNOTES') or die('No direct access allowed');

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

		$return = $protocol.'://'.$host.dirname($self).'/';

		return $return;
	}

	public static function getSettings() : array
	{
		// Retrieve the root folder based on this script
		// Make a lot of dirname() since this current script
		// is deep in the marknotes folder's structure
		$webRoot=trim(dirname($_SERVER['SCRIPT_FILENAME']), DS);
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
			if (is_file($json = $webRoot.$file)) {
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

		$arr = array(
			'root'=>$webRoot,
			'docs'=>$webRoot.$docs,
			'url'=>$url,
			'title'=>$title,
			'locale'=>$locale,
			'debug'=>$debug
		);

		return $arr;
	}
}
