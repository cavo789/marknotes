<?php
/**
 * Based on PHPFastCache
 *
 * @link https://github.com/PHPSocialNetwork/phpfastcache
 *
 * The cache system will be enabled only if enabled in settings.json
 * See plugins.options.task.optimize.cache.enabled
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

		// Only if enabled in settings.json
		$options = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$enabled = $options['enabled']??1;

		if ($enabled) {
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
					file_put_contents($fname, $content = '# marknotes - Deny '.
						'access to this folder'.PHP_EOL.'deny from all');
				}

				// Setup File Path on your config files
				CacheManager::setDefaultConfig(
					array('path' => static::$sCacheFolder)
				);

				// files : can raise a server error (500) in some cases, need
				// 		furthers investigations (permissions, owner, ... ) ?
				//static::$InstanceCache = CacheManager::getInstance('files');
				//static::$InstanceCache = CacheManager::getInstance('cookie');
				static::$InstanceCache = CacheManager::getInstance('files');

				if (!isset(static::$InstanceCache)) {
					throw new Exception('Marknotes - Cache library not '.
						'loaded.');
				}

			} // if ($folder!=='')

		}

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

	public static function deleteItem(string $key) {
		if (!static::$InstanceCache==null) {
			static::$InstanceCache->deleteItem($key);
		}
	}

	public static function deleteItemsByTag(string $tag) {
		if (!static::$InstanceCache==null) {
			static::$InstanceCache->deleteItemsByTag($tag);
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
