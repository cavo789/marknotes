<?php
/**
 * Search engine, search for keywords in notes and return the md5
 * of the filename. Then, the treeview (jstree) will filter on that
 * list and only show items with the same md5
 *
 * Answer to /search.php?str=Marknotes or
 * index.php?task=task.search.search&str=Marknotes
 *
 * Parameters :
 *
 * 	- str=xxxx	: the keyword to search
 * 	- disable_cache=1 : Don't use the cache (slower)
 * 	- disable_plugins=1 : Don't execute plugins (faster)
 * 	- restrict_folder=XXX (base64_ecoded) - Restrict to that folder
 *
 * Using https://github.com/PHPSocialNetwork/phpfastcache
 */

namespace MarkNotes\Plugins\Task\Search;

defined('_MARKNOTES') or die('No direct access allowed');

class Search extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.search';
	protected static $json_options = 'plugins.options.task.search';

	/**
	 * Get the list of notes, relies on the listFiles
	 * task plugin for this in order to, among other things,
	 * be sure that only files that the
	 * user can access are retrieved and not confidential ones
	 */
	private static function getFiles(string $restrict_folder = '') : array
	{
		$arrFiles = [
			'params' => [
				'restrict_folder' => $restrict_folder
			],
			'result' => []
		];

		// Call the listfiles.get event and initialize $arrFiles
		$aeEvents = \MarkNotes\Events::getInstance();
		$args = [&$arrFiles];
		$aeEvents->loadPlugins('task.listfiles.get');
		$aeEvents->trigger('task.listfiles.get::run', $args);

		return $args[0];
	}

	/**
	 * Used when no keyword has been mentionned on the url
	 * (f.i. http://localhost/notes/search.php?str=)
	 */
	private static function noParam() : boolean
	{
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeDebug = \MarkNotes\Debug::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('No pattern has been specified. The str=keyword parameter was missing', 'debug');
		}
		/*<!-- endbuild -->*/

		// Nothing should be returned, the list of files can be immediatly displayed
		header('Content-Type: application/json');
		die('[]');

		return false;
	}

	/**
	 * Get the content of the cache
	 * @return string
	 */
	private static function getFromCache(string $pattern)
	{
		$return = null;

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);
		$bCache = $arrSettings['cache']['enabled'] ?? false;

		// Allow to override the "cache_search_results" setting
		// Read, on the querystring, if there is a ?disable_cache parameter
		// Can we use the cache system ? Default is false (use the cache)
		$disableCache = $aeFunctions->getParam('disable_cache', 'bool', false);

		if ($bCache) {
			// Read from the cache
			$aeCache = \MarkNotes\Cache::getInstance();

			if ($disableCache) {
				// Disable cache ? Remove the current object so we won't
				// use the cache and will create a new one
				$return = $aeCache->deleteItem(md5($pattern));
			}

			$return = $aeCache->getItem(md5($pattern));
		} // if ($bCache)

		return $return;
	}

	/**
	 * Don't search into hidden files i.e. files in a folder
	 * with a name starting with a dot (like ".files") or
	 * filename starting with a dot (like ".secrets.md")
	 *
	 * @param  array $arrFiles [description]
	 * @return array [description]
	 */
	private static function removeHiddenFiles(array $arrFiles) : array
	{
		$arrTmp = [];
		foreach ($arrFiles as $key => $value) {
			if (strpos($value, DS . '.') === false) {
				$arrTmp[$key] = $value;
			}
		}

		return $arrTmp;
	}

	/**
	 * Make the search.
	 */
	private static function doSearch(array $keywords, string $pattern, array $params) : array
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeMarkdown = \MarkNotes\FileType\Markdown::getInstance();

		/*<!-- build:debug -->*/
		$debug_output = '';
		/*<!-- endbuild -->*/

		// Retrieve the list of files
		$arrFiles = self::getFiles($params['restrict_folder']);

		$arrFiles = self::removeHiddenFiles($arrFiles);

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			if (!$aeFunctions->isAjaxRequest()) {
				$debug_output .= '<p>Number of files (excluding hidden files/folders): ' . count($arrFiles) . '</h1>';
			}
		}
		/*<!-- endbuild -->*/
		// Absolute root folder
		$root = $aeSettings->getFolderWebRoot();

		$bDebug = false;

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$bDebug = true;
		}
		/*<!-- endbuild -->*/

		$return = [];

		// Filenames in $arrFiles already start with the
		// "docs/" part so, f.i.
		// 		docs/Development/markdown.md
		foreach ($arrFiles as $file) {
			// Remove the .md extension since that extension
			// wasn't used when building the treeview.
			// We, absolutly, need to respect the same way to
			// build the MD5 of the matched file.
			// Should be something like docs/subfolder/file
			// (without the extension)

			// Just keep relative filenames, relative from the
			// /docs folder
			//$file = str_replace($docFolder, '', $file);

			// If the keyword can be found in the document title,
			// yeah, it's the fatest solution,
			// return that filename
			foreach ($keywords as $keyword) {
				$bFound = true;
				if (stripos($file, $keyword) === false) {
					// at least one term is not present in
					// the filename, stop
					$bFound = false;
					break;
				}
			} // foreach ($keywords as $keyword)

			if ($bFound) {
				// Found in the filename => stop process of
				// this file

				/*<!-- build:debug -->*/
				if ($bDebug) {
					if (!$aeFunctions->isAjaxRequest()) {
						$debug_output .= '<li>FOUND IN [' . $file . ']</li>';
					}
					$aeDebug->log('	FOUND IN [' . $file . ']', 'debug');
				}
				/*<!-- endbuild -->*/

				// Don't remember the extension
				$return[] = $aeFiles->removeExtension($file);
			} else { // if ($bFound)
				// Open the file and check against its content
				// (plain and encrypted)
				$fullname = $root . $file;

				// Read the note content
				if ($params['disable_plugins'] == 0) {
					// The read() method will fires any plugin linked
					// to the markdown.read event
					// so encrypted notes will be automatically unencrypted
					$params['filename'] = $fullname;
					$params['encryption'] = 0;
					$content = $aeMarkdown->read($fullname, $params);
				} else {
					// Don't fire plugins for speed purposes
					// Immediatly read the file's content
					$content = file_get_contents($fullname);
				}

				$bFound = true;

				foreach ($keywords as $keyword) {
					/**
					 * Add "$file" which is the filename in the
					 * content, just for the search.
					 * Because when f.i. search for two words;
					 * one can be in the filename and one in the
					 * content.
					 * By searching only in the content; that
					 * file won't
					 * appear while it should be the Collapse
					 * so "fake" and add the filename in the content,
					 * just for the search_no_result
					 */
					if (stripos($file . '#@#§§@' . $content, $keyword) === false) {
						// at least one term is not present in the content, stop
						$bFound = false;
						break;
					}
				} // foreach($keywords as $keyword)

				if ($bFound) {
					/*<!-- build:debug -->*/
					if ($bDebug) {
						if (!$aeFunctions->isAjaxRequest()) {
							$debug_output .= '<li>FOUND IN [' . $file . ']</li>';
						}
						$aeDebug->log('	FOUND IN [' . $file . ']', 'debug');
					}
					/*<!-- endbuild -->*/

					// Found in the filename =>
					// stop process of this file
					// Don't remember the extension
					$return[] = $aeFiles->removeExtension($file);
				}  // if ($bFound)
			} // if ($bFound) {
		} // foreach ($arrFiles as $file)

		$arr = [];
		$arr['from_cache'] = 0;
		$arr['pattern'] = json_encode($pattern);
		$arr['files'] = json_encode(array_map('md5', $return));

		/*<!-- build:debug -->*/
		if ($debug_output !== '') {
			echo $debug_output;
		}
		/*<!-- endbuild -->*/

		return $arr;
	}

	/**
	 * $params['encryption'] = 0 : encrypted data should be unencrypted
	 *						 1 : encrypted infos should stay encrypted
	 */
	public static function run(&$params = null) : bool
	{
		$startedAt = microtime(true);

		$aeCache = \MarkNotes\Cache::getInstance();
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// String to search (can be something like
		// 'invoices,2017,internet') i.e. multiple keywords
		$pattern = trim($aeFunctions->getParam('str', 'string', '', false, SEARCH_MAX_LENGTH));

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			if (!$aeFunctions->isAjaxRequest()) {
				echo '<h1>Start searching for **' . $pattern . '**</h1>';
				echo '<em>Appears only in debug mode and when not started by an ajax request</em>';
			}
		}
		/*<!-- endbuild -->*/

		if ($pattern === '') {
			self::noParam();
		}

		// search will be case insensitive
		$pattern = strtolower($pattern);

		// $keywords can contains multiple terms like
		// 'php,avonture,marknotes'.
		// Search for these three keywords (AND)
		$keywords = explode(',', rtrim($pattern, ','));

		// Speed : be sure to have the same keyword only once
		$keywords = $aeFunctions->array_iunique($keywords);

		// Sort keywords so the pattern will always be sorted
		// If $pattern was 'php,avonture,marknotes', thanks
		// the sort, it will be avonture,php,marknotes.
		// Whatever the order, as from here, the $pattern
		// will always be sorted (=> optimization)
		sort($keywords);
		$pattern = implode($keywords, ',');

		// The cache can vary from one user to an
		// another so we need to use his username
		$pattern = $aeSession->getUser() . '###' . $pattern;

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('Searching for [' . $pattern . ']', 'debug');
		}
		/*<!-- endbuild -->*/

		// Restrict folder will allow to limit the search to a given
		// subfolder and not search for everyting under /docs
		$restrict_folder = trim(urldecode($aeFunctions->getParam('restrict_folder', 'string', '', true)));
		$restrict_folder = json_decode($restrict_folder);

		if ($restrict_folder == null) {
			$restrict_folder = '';
		} else {
			if ($restrict_folder == '.') {
				// Restrict on "." means everything so no restriction
				$restrict_folder = '';
			}
		}

		$params['restrict_folder'] = $restrict_folder;

		// Look for the ""&disable_plugins" querystring variable
		// If set and if on 1, plugins won't be fired; the search will be
		// faster than running every plugins for every files
		// Speed can be x10
		$disable_plugins = $aeFunctions->getParam('disable_plugins', 'boolean', false);
		$params['disable_plugins'] = $disable_plugins ? 1 : 0;

		$arr = null;

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;

		if ($bCache) {
			$cached = self::getFromCache(md5($pattern));
			if (!is_null($cached)) {
				$arr = $cached->get();
			}
		}

		if (is_null($arr)) {
			$arr = self::doSearch($keywords, $pattern, $params);
			$arr['message'] = '';

			if ($bCache) {
				$arr['from_cache'] = 1;

				// Cache the result; read duration from settings.json
				$duration = $arrSettings['duration']['default'];
				$cached->set($arr)->expiresAfter($duration)->addTag(md5('search'));
				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}
		} else {
			$arr['message'] = $aeSettings->getText('search_from_cache');

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('	Retrieving from the cache', 'debug');
			}
			/*<!-- endbuild -->*/
		}

		$arr['restrict_folder'] = $params['restrict_folder'];
		$arr['disable_plugins'] = $params['disable_plugins'];

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			if (!$aeFunctions->isAjaxRequest()) {
				$timeTaken = microtime(true) - $startedAt;
				echo '<h5>Time taken : ' . $timeTaken . ' seconds</h5>';
				echo '<pre>' . json_encode($arr, JSON_PRETTY_PRINT) . '</pre>';
				die();
			}
		}
		/*<!-- endbuild -->*/

		// Nothing should be returned, the list of files
		// can be immediatly displayed
		header('Content-Type: application/json');
		// Don't return filenames but the md5() of these names
		echo json_encode($arr);

		return true;
	}
}
