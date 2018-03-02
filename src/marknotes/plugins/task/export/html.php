<?php
/**
 * Export the note as a .html file
 *
 * The HTML version will be put in the cache folder but ONLY WHEN
 * the note will not contains encrypted informations. If it's the
 * case, HTML can't be put in the cache otherwise we'll store
 * unencrypted informations which is a bad idea.
 */

namespace MarkNotes\Plugins\Task\Export;

defined('_MARKNOTES') or die('No direct access allowed');

class HTML extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.html';
	protected static $json_options = '';

	private static $extension = 'html';

	/**
	 * Make the conversion
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$doc = $aeSettings->getFolderDocs(true);

		$fullname = $params['filename'].'.md';
		$fullname = $doc.ltrim(str_replace('/', DS, $fullname), DS);

		$final = $aeFiles->removeExtension($params['filename']).'.'.static::$extension;
		$final = $aeSettings->getFolderDocs(true).$final;

		$html = '';

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = boolval($arrSettings['enabled'] ?? false);

		if ($bCache) {
			$aeCache = \MarkNotes\Cache::getInstance();

			// The list of tags can vary from one user to an
			// another so we need to use his username
			$key = $aeSession->getUser().'###'.
				$params['filename'];

			$cached = $aeCache->getItem(md5($key));
			$data = $cached->get();
			$html = $data['html']??'';
		}

		if (trim($html) == '') {
			// Get the HTML content
			$aeTask = \MarkNotes\Tasks\Display::getInstance();
			$html = $aeTask->run($params);

			if (trim($html)=='') {
				// No cache if the HTML is empty, probably due
				// to an error
				$bCache = false;
			} else if (strpos($html, 'data-encrypt="true"')>0) {
				// Check if the HTML contains the data-encrypt="true"
				// attribute.
				// If yes, this means that this note contains encrypted
				// informations and if we store the note in the cache,
				// we'll store the unencrypted data ==> DON'T DO THIS
				$bCache = false;
			}

			if ($bCache) {
				// Save the list in the cache
				$arr = array();
				$arr['from_cache'] = 1;
				$arr['html'] = $html;
				// Get the duration for the HTML cache (default : 31 days)
				$duration = $arrSettings['duration']['html'];

				// Add a tag to the cached item : the fullname of the
				// note so we can kill with a
				// $aeCache->deleteItemsByTag(md5($fullname));
				// every cached items concerning this note
				$cached->set($arr)->expiresAfter($duration)->addTag(md5($fullname));
				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}
		} else { // if ($html == '')
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("	Retrieved from cache [".$key."]","debug");
			}
			/*<!-- endbuild -->*/

			// Debug : add a meta cache=1 just after the <head> tag
			// Get the start position of the tag
			preg_match('~<head.*~', $html, $matches, PREG_OFFSET_CAPTURE);
			$pos = $matches[0][1];
			// Get the ">" character so we can know where <head> is
			// positionned since, perhaps, there are a few attributes
			$pos = strpos($html, '>', $pos) + 1;

			// Ok, insert the new meta
			$meta = '<meta name="cached" content="1">';
			$html = substr_replace($html, $meta, $pos, 0);
		}

		$params['content'] = $html;

		return true;
	}
}
