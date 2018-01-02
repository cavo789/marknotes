<?php
/**
 * Based on PHPFastCache
 *
 * @link https://github.com/PHPSocialNetwork/phpfastcache
 */

/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

use \phpFastCache\CacheManager;
use \phpFastCache\Core\phpFastCache;

class Cache
{
	protected static $hInstance = null;
	protected static $InstanceCache = null;
	protected static $sCacheFolder = '';

	public function __construct(string $folder)
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Initialize the cache folder
		if ($folder!=='') {
			static::$sCacheFolder = rtrim($folder, DS).DS.'cache';

			if (!is_dir(static::$sCacheFolder)) {
				mkdir(static::$sCacheFolder, CHMOD_FOLDER);
			}

			if (!is_file($fname = static::$sCacheFolder.'/.gitignore')) {
				file_put_contents($fname, '# Ignore everything'.PHP_EOL.'*');
			}

			if (!is_file($fname = static::$sCacheFolder.'/.htaccess')) {
				file_put_contents($fname, 'deny from all');
			}

			// Setup File Path on your config files
			CacheManager::setDefaultConfig(
				array('path' => static::$sCacheFolder)
			);

			// In your class, function, you can call the Cache
			static::$InstanceCache = CacheManager::getInstance('files');

			if (!isset(static::$InstanceCache)) {
				throw new Exception('Marknotes - Cache library not '.
					'loaded.');
			}

		} // if ($folder!=='')

		return true;
	}

	public static function getInstance(string $folder = '')
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Cache($folder);
		}
		return self::$hInstance;
	}

	/**
	 * Clear everything from the cache
	 */
	public static function clear() {
		if (!static::$InstanceCache==null) {
			static::$InstanceCache->clear();
		}
	}

	/**
	 * Get the cache content for $key
	 */
	public static function getItem(string $key)
	{
		if (!static::$InstanceCache==null) {
			return static::$InstanceCache->getItem($key);
		}
	}

	/**
	 * Save an information into the cache
	 */
	public static function save($info) {
		if (!static::$InstanceCache==null) {
			return static::$InstanceCache->save($info);
		} else {
			return false;
		}
	}
}
