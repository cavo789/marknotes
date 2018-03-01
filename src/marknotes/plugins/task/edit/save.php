<?php
/**
 * Edit form - Handle the Save action
 */
namespace MarkNotes\Plugins\Task\Edit;

defined('_MARKNOTES') or die('No direct access allowed');

class Save extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.edit';
	protected static $json_options = '';

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			$aeSession = \MarkNotes\Session::getInstance();
			$bCanRun = boolval($aeSession->get('authenticated', 0));
		}

		if (!$bCanRun) {
			$aeSettings = \MarkNotes\Settings::getInstance();

			$return = array();
			$return['status'] = 0;
			$return['message'] = $aeSettings->getText('not_authenticated', 'You need first to authenticate', true);

			header('Content-Type: application/json');
			echo json_encode($return, JSON_PRETTY_PRINT);
		}

		return $bCanRun;
	}

	/*
	 * Save new content (after edition by the user)
	 * Called by the editor, responds to the save button.
	 */
	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('Saving the note\'s content', 'debug');
		}
		/*<!-- endbuild -->*/

		// Get the filename from the querystring
		$filename = $aeFunctions->getParam('param', 'string', '', true);
		$filename = json_decode(urldecode($filename));

		// Be sure to have the .md extension
		$filename = $aeFiles->RemoveExtension($filename).'.md';

		// Make filename absolute
		$fullname = $aeFiles->makeFileNameAbsolute($filename);

		if (!$aeFiles->exists($fullname)) {
			echo str_replace('%s', '<strong>'.$filename.'</strong>', $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists'));
			die();
		}

		$markdown = json_decode(urldecode($aeFunctions->getParam('markdown', 'string', '', true)));

		// Be sure to have content with LF and not CRLF in order to be able to use
		// generic regex expression (match \n for new lines)
		$markdown = str_replace("\r\n", "\n", $markdown);

		// Rewrite the file on the disk
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('task.markdown.write');
		$params = array('markdown'=>$markdown);
		$args = array(&$params);
		$aeEvents->trigger('task.markdown.write::run', $args);

		// Remember the markdown content
		$content = $args[0]['markdown'];

		// We can reset the last_added_note at the first save
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSession->set('last_added_note', '');

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = $arrSettings['enabled'] ?? false;

		if ($bCache) {
			$aeCache = \MarkNotes\Cache::getInstance();

			// Clear the cache for this note : clear every cached
			// items with a tag equal to $fullname i.e. the fullname
			// of the note
			$aeCache->deleteItemsByTag(md5($fullname));

			// And create a new markdown content in the cache
			$key = 'task.markdown.read###'.$fullname;
			$cached = $aeCache->getItem(md5($key));
			$arr = array();
			$arr['markdown'] = $content;
			$arr['from_cache'] = 1;
			$duration = $arrSettings['duration']['html'];
			$cached->set($arr)->expiresAfter($duration)->addTag(md5($fullname));
			$aeCache->save($cached);
		}

		$status = array('status' => 1,'message' => $aeSettings->getText('button_save_done', 'The file has been successfully saved'));

		header('Content-Type: application/json; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo json_encode($status, JSON_PRETTY_PRINT);

		return true;
	}
}
