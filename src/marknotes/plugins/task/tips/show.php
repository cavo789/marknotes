<?php
/**
 * Show tips - Homepage
 *
 * The interface is displayed and no note is displayed so,
 * to help new users, display a quick userguide
 *
 * Answer to index.php?task=task.tips.show&tip=homepage
 */
namespace MarkNotes\Plugins\Task\Tips;

defined('_MARKNOTES') or die('No direct access allowed');

class Show
{
	public static function run(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$tip = trim($aeFunctions->getParam('param', 'string', '', false));
		$tip = $aeFiles->sanitizeFileName($tip);

		header('Content-Transfer-Encoding: ascii');
		header('Content-Type: text/html; charset=utf-8');

		$filename = __DIR__.'/tips/'.$tip.'.html';
		$html = '';

		if (is_file($filename)) {
			$html = '<h1>Quick user guide</h1>';
			$html .= file_get_contents($filename);

			// Replace variables
			$docs = rtrim($aeSettings->getFolderDocs(true), DS);
			$html = str_replace('%DOCS%', $docs, $html);
			$html = str_replace('%GITHUB%', GITHUB_REPO, $html);
		} else {
			$html = '<p class="error">Sorry the '.str_replace(__DIR__, '', $filename).' doesn\'t exists</p>';
		}

		echo $html;
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
