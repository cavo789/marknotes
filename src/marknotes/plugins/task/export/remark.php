<?php
/**
 * What are the actions to fired when MarkNotes is running the "remark" task ?
 */

namespace MarkNotes\Plugins\Task\Export;

defined('_MARKNOTES') or die('No direct access allowed');

class Remark extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.remark';
	protected static $json_options = 'plugins.options.page.html.remark';

	private static $extension = 'remark';

	/**
	 * Retrieve the template for the presentation and use it
	 */
	private static function getTemplate(array $params = array()) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeHTML = \MarkNotes\FileType\HTML::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(), '/');

		$template = $aeSettings->getTemplateFile(static::$extension);

		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('Template used : '.$root.$template);
		}

		if ($template === '') {
			$aeFunctions->fileNotFound($template);
		}

		$template = $aeFiles->getContent($template);

		return $template;
	}

	private static function getHTML(array $params) : string
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();

		// If the filename doesn't mention the file's
		// extension, add it.
		if (substr($params['filename'], -3) != '.md') {
			$params['filename'] = $aeFiles->removeExtension($params['filename']).'.md';
		}

		$aeEvents->loadPlugins('content.slides.remark');
		$args = array(&$params);
		$aeEvents->trigger('content.slides.remark::run', $args);
		$content = $args[0]['html'];

		return $params['html'];
	}

	public static function run(&$params = null) : bool
	{
		$bReturn = true;

		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = boolval($arrSettings['enabled'] ?? false);

		$html = '';

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
			$html = self::getHTML($params);

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
				$aeDebug->log("    Retrieved from cache [".$key."]","debug");
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
