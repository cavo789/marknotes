<?php
/**
 * Display the sitemap
 */

namespace MarkNotes\Plugins\Task\Sitemap;

defined('_MARKNOTES') or die('No direct access allowed');

class Get
{
	/**
	 * Get the list of notes, relies on the listFiles task plugin for this
	 * in order to, among other things, be sure that only files that the
	 * user can access are retrieved and not confidential ones
	 */
	private static function getFiles() : array
	{
		$arrFiles = array();

		// Call the listfiles.get event and initialize $arrFiles
		$aeEvents = \MarkNotes\Events::getInstance();
		$args=array(&$arrFiles);
		$aeEvents->loadPlugins('task.listfiles.get');
		$aeEvents->trigger('task.listfiles.get::run', $args);

		return $args[0];
	}

	private static function doGetSitemap() : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrFiles = self::getFiles();


		$xml = '';

		$folder = str_replace('/', DS, $aeSettings->getFolderDocs(true));

		foreach ($arrFiles as $file) {
			$relFileName = str_replace($folder, '', $file);

			$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

			$urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($relFileName, 'html'));

			//if (!$aeFiles->exists($file)) {
			//	$file = utf8_decode($file);
			//}

			// filemtime will raise an error when the filename contains
			// f.i. accentuated characters
			$lastModified = @filemtime($file);
			//if ($lastModified == null) {
			//	$lastModified = filemtime(utf8_decode($file));
			//}

			$xml .=
				'      <url>'.PHP_EOL.
				'         <loc>'.str_replace(' ', '%20', htmlspecialchars($urlHTML, ENT_HTML5)).'</loc>'.PHP_EOL.
				'         <lastmod>'.date('Y-m-d\TH:i:sP', $lastModified).'</lastmod>'.PHP_EOL.
				'         <changefreq>weekly</changefreq>'.PHP_EOL.
				'         <priority>1.0</priority>'.PHP_EOL.
				'      </url>'.PHP_EOL;
		} // foreach

		$xml =
			'<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
			'<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
				'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" '.
				'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
			'   '.$xml.PHP_EOL.
			'</urlset>';

		return $xml;
	}

	public static function run(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arr = null;

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log("Get the sitemap","debug");
		}
		/*<!-- endbuild -->*/

		if ($bCache) {
			$aeCache = \MarkNotes\Cache::getInstance();

			// The list of files can vary from one user to an
			// another so we need to use his username
			$key = $aeSession->getUser().'###sitemap';

			$cached = $aeCache->getItem(md5($key));
			$arr = $cached->get();
		}

		if (is_null($arr)) {
			$arr['xml'] = self::doGetSitemap();

			if ($bCache) {
				// Save the list in the cache
				$arr['from_cache'] = 1;
				// Default : 7 days.
				$duration = $arrSettings['duration']['sitemap'];
				$cached->set($arr)->expiresAfter($duration);
				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}
		} else {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('   Retrieving from the cache', 'debug');
			}
			/*<!-- endbuild -->*/
		} // if (is_null($arr))

		// Nothing should be returned, the xml can be displayed immediatly
		header('Content-Type: application/xml; charset=utf-8');
		echo $arr['xml'];

		return true;
	}

	/**
	 * Attach the function and responds to events
	 */
	public function bind(string $task)
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->bind('run', __CLASS__.'::run', $task);
		return true;
	}
}
