<?php
/**
 * Homepage will allow to show extra informations and guidance
 * to the user who start with marknotes.
 *
 * Answer to URL like index.php?task=task.homepage.show
 */
namespace MarkNotes\Plugins\Task\Homepage;

defined('_MARKNOTES') or die('No direct access allowed');

class Show extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.homepage';
	protected static $json_options = 'plugins.options.task.homepage';

	/**
	 * Retrieve the tip
	 */
	private static function doGetTip(string $tip) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get options of the cache plugin
		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;

		// Get options of the tips plugin : retrieve the name of
		// the note to show for the 'tip' (f.i. "homepage")
		$filename = trim(self::getOptions($tip, ''));

		if ($filename !== '') {
			$filename = $aeSettings->getFolderDocs().$filename;

			if (!$aeFiles->exists($filename)) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("File ".$filename." not found", "debug");
				}
				/*<!-- endbuild -->*/
				$filename = '';
			}
		}

		if ($filename == '') {
			$filename = __DIR__.'/tips/'.$tip.'.html';
		}

		$html = '';
		$arr = null;

		if ($bCache) {
			$aeCache = \MarkNotes\Cache::getInstance();
			$key = 'tips###'.$tip;

			$cached = $aeCache->getItem(md5($key));
			$arr = $cached->get();
		}

		if (is_null($arr)) {
			$html='';

			$aeFiles = \MarkNotes\Files::getInstance();
			if ($aeFiles->exists($filename)) {
				if ($aeFiles->getExtension($filename)=='md') {
					// Read the markdown note and run any
					// markdown plugins
					$aeMarkdown = \MarkNotes\FileType\Markdown::getInstance();
					$content = $aeMarkdown->read($filename);

					// And convert MD to HTML
					$aeConvert = \MarkNotes\Helpers\Convert::getInstance();
					$html = $aeConvert->getHTML($content, array(), true);
				} else {
					$html = $aeFiles->getContent($filename);
				}

				$html .= '<hr/>';

				// Replace variables
				$docs = rtrim($aeSettings->getFolderDocs(true), DS);
				$html = str_replace('%DOCS%', $docs, $html);
				$html = str_replace('%GITHUB%', GITHUB_REPO, $html);
			} else {
				$html = '<p class="error">Sorry the '.str_replace(__DIR__, '', $filename).' doesn\'t exists</p>';
			}

			$arr['tip'] = $html;

			if ($bCache) {
				// Save the list in the cache
				$arr['from_cache'] = 1;
				$duration = $arrSettings['duration']['default'];
				$cached->set($arr)->expiresAfter($duration);
				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}
		} else {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('	Retrieving from the cache', 'debug');
			}
			/*<!-- endbuild -->*/
		} // if (is_null($arr))

		return $arr['tip'];
	}

	public static function run(&$params = null) : bool
	{
		if (self::isEnabled(false)) {
			// Ok, the task is enabled

			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$tip = trim($aeFunctions->getParam('param', 'string', '', false));
			$tip = $aeFiles->sanitize($tip);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("Get tip [".$tip."]", "debug");
			}
			/*<!-- endbuild -->*/

			$html = self::doGetTip($tip);

			header('Content-Transfer-Encoding: ascii');
			header('Content-Type: text/html; charset=utf-8');
			echo $html;
		}

		die();
	}
}
