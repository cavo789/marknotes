<?php
/**
 * Show tips
 *
 * Show tip will allow to show extra informations and guidance
 * to the user who start with marknotes.
 *
 * The "which tip should be displayed" is answered by the &tip
 * parameter (f.i. &tip=homepage). The text of the tip can then
 * be retrieved in the /tips subfolder.
 *
 * Answer to URL like index.php?task=task.tips.show&param=homepage
 */
namespace MarkNotes\Plugins\Task\Tips;

defined('_MARKNOTES') or die('No direct access allowed');

class Show
{

	/**
	 * Retrieve the tip
	 */
	private static function doGetTip(string $tip) : string
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;

		$html = '';
		$arr = null;

		if ($bCache) {
			$aeCache = \MarkNotes\Cache::getInstance();
			$key = 'tips###'.$tip;

			$cached = $aeCache->getItem(md5($key));
			$arr = $cached->get();
		}

		if (is_null($arr)) {
			$filename = __DIR__.'/tips/'.$tip.'.html';

			$aeFiles = \MarkNotes\Files::getInstance();
			if ($aeFiles->exists($filename)) {
				$html =
					'<h1>Quick user guide</h1>'.
					'<div class="show_tip">'.
						$aeFiles->getContent($filename).
					'</div>';

				$html .= '<hr/><input type="checkbox" id="show_tips" '.
				 	'checked="checked" data-task="settings" '.
					'data-key="interface.show_tips"> '.
					'Click here to hide tips in '.
					'the future';

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
				$aeDebug->log('   Retrieving from the cache', 'debug');
			}
			/*<!-- endbuild -->*/
		} // if (is_null($arr))

		return $arr['tip'];
	}

	public static function run(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arr = $aeSettings->getPlugins('/interface', array('show_tips'=>1));
		$show_tips = boolval($arr['show_tips']);

		if ($show_tips) {

			$tip = trim($aeFunctions->getParam('param', 'string', '', false));
			$tip = $aeFiles->sanitize($tip);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("Get tip [".$tip."]","debug");
			}
			/*<!-- endbuild -->*/

			$html = self::doGetTip($tip);

			header('Content-Transfer-Encoding: ascii');
			header('Content-Type: text/html; charset=utf-8');
			echo $html;
		}
		die();
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
