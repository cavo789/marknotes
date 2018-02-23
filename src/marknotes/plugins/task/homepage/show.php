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
	 * Retrieve the note content
	 */
	private static function doGetNote(string $filename) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get options of the cache plugin
		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;

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

		$html = '';
		$arr = null;

		if ($bCache) {
			$aeCache = \MarkNotes\Cache::getInstance();
			$key = 'homepage###'.$filename;

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

			} else {
				$html = '<p class="error">Sorry the '.str_replace(__DIR__, '', $filename).' doesn\'t exists</p>';
			}

			$arr['homepage'] = $html;

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

		return $arr['homepage'];
	}

	public static function run(&$params = null) : bool
	{
		if (self::isEnabled(false)) {
			// Ok, the task is enabled

			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$note = trim(self::getOptions('note',''));

			if ($note !== '') {

				$docs = $aeSettings->getFolderDocs(true);
				$note = str_replace('/', DS, $docs.$note);

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("Homepage, show [".$note."]", "debug");
				}
				/*<!-- endbuild -->*/

				$html = self::doGetNote($note);

				header('Content-Transfer-Encoding: ascii');
				header('Content-Type: text/html; charset=utf-8');
				echo $html;
			}
		}

		die();
	}
}
