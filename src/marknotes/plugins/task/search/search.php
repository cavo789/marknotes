<?php
/**
 * Search engine, search for keywords in notes and return the md5
 * of the filename. Then, the treeview (jstree) will filter on that
 * list and only show items with the same md5
 *
 * Answer to /search.php?str=Marknotes or
 * index.php?task=task.search.search&str=Marknotes
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
		$arrFiles = array(
			'params'=>array(
				'restrict_folder'=>$restrict_folder
			),
			'result'=>array()
		);

		// Call the listfiles.get event and initialize $arrFiles
		$aeEvents = \MarkNotes\Events::getInstance();
		$args=array(&$arrFiles);
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
		// Read, on the querystring, if there is a ?cache parameter
		// Can we use the cache system ? Default is true
		$useCache = $aeFunctions->getParam('cache', 'bool', true);

		if ($bCache) {
			// Read from the cache
			$aeCache = \MarkNotes\Cache::getInstance();

			if ($useCache) {
				$return = $aeCache->getItem(md5($pattern));
			}
		} // if ($bCache)

		return $return;
	}

	/**
	 * Make the search.
	 */
	private static function doSearch(array $keywords, string $pattern, string $restrict_folder = '') : array
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeMarkdown = \MarkNotes\FileType\Markdown::getInstance();

		// Retrieve the list of files
		$arrFiles = self::getFiles($restrict_folder);

		// Absolute root folder
		$root = $aeSettings->getFolderWebRoot();

		$bDebug=false;
		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$bDebug=true;
		}
		/*<!-- endbuild -->*/

		$return = array();

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
					$aeDebug->log("	FOUND IN [".$file."]", "debug");
				}
				/*<!-- endbuild -->*/

				// Don't remember the extension
				$return[] = $aeFiles->removeExtension($file);
			} else { // if ($bFound)
				// Open the file and check against its content
				// (plain and encrypted)
				$fullname = $root.$file;

				// Read the note content
				// The read() method will fires any plugin linked
				// to the markdown.read event
				// so encrypted notes will be automatically unencrypted

				$params['filename']=$fullname;
				$params['encryption'] = 0;
				$content = $aeMarkdown->read($fullname, $params);

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
					if (stripos($file.'#@#§§@'.$content, $keyword) === false) {
						// at least one term is not present in the content, stop
						$bFound = false;
						break;
					}
				} // foreach($keywords as $keyword)

				if ($bFound) {
					/*<!-- build:debug -->*/
					if ($bDebug) {
						$aeDebug->log("	FOUND IN [".$file."]", "debug");
					}
					/*<!-- endbuild -->*/

					// Found in the filename =>
					// stop process of this file
					// Don't remember the extension
					$return[] = $aeFiles->removeExtension($file);
				}  // if ($bFound)
			} // if ($bFound) {
		} // foreach ($arrFiles as $file)

		$arr = array();
		$arr['from_cache'] = 0;
		$arr['pattern'] = $pattern;
		$arr['files'] = json_encode(array_map("md5", $return));

		return $arr;
	}

	/**
	* $params['encryption'] = 0 : encrypted data should be unencrypted
	*						 1 : encrypted infos should stay encrypted
	*/
 	public static function run(&$params = null) : bool
	{
		$aeCache = \MarkNotes\Cache::getInstance();
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// String to search (can be something like
		// 'invoices,2017,internet') i.e. multiple keywords
		$pattern = trim($aeFunctions->getParam('str', 'string', '', false, SEARCH_MAX_LENGTH));

		if ($pattern==='') {
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

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('Searching for ['.$pattern.']', 'debug');
		}
		/*<!-- endbuild -->*/

		// Restrict folder will allow to limit the search to a given
		// subfolder and not search for everyting under /docs
		$restrict_folder = trim(urldecode($aeFunctions->getParam('restrict_folder', 'string', '', true)));
		$restrict_folder = json_decode($restrict_folder);
		if ($restrict_folder==null) $restrict_folder='';

		$arr = null;

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;

		if ($bCache) {
			// The cache can vary from one user to an
			// another so we need to use his username
			$key = $aeSession->getUser().'###'.$pattern;
			$cached = self::getFromCache(md5($key));
			if (!is_null($cached)) {
				$arr = $cached->get();
			}
		}

		if (is_null($arr)) {
			$arr = self::doSearch($keywords, $pattern, $restrict_folder);

			if ($bCache) {
				$arr['from_cache'] = 1;
				// Cache the result; read duration from settings.json
				$duration = $arrSettings['duration']['default'];
				$cached->set($arr)->expiresAfter($duration);
				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}
		} else {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('	Retrieving from the cache', 'debug');
			}
			/*<!-- endbuild -->*/
		}

		// Nothing should be returned, the list of files
		// can be immediatly displayed
		header('Content-Type: application/json');
		// Don't return filenames but the md5() of these names
		echo json_encode($arr);

		return true;
	}
}
