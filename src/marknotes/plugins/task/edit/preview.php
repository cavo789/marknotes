<?php
/**
 * Edit form - Handle the Preview action and return the HTML content
 * of the markdown note but don't modify / save the file on the disk,
 * just render the HTML and return it
 */
namespace MarkNotes\Plugins\Task\Edit;

defined('_MARKNOTES') or die('No direct access allowed');

class Preview extends \MarkNotes\Plugins\Task\Plugin
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
	 * Return the HTML of the edited note; don't save.
	 * Called by the editor, responds to the preview button.
	 */
	public static function run(&$params = null) : bool
	{
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug->log('Preview the note\'s content', 'debug');
		}
		/*<!-- endbuild -->*/

		// Get the filename from the querystring
		$filename = $aeFunctions->getParam('param', 'string', '', true);

		if ($filename=='') {
			echo $aeSettings->getText('error_filename_missing', 'Error - a filename is expected and none has been specified');
			die();
		}

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

		// Be sure to have content with LF and not CRLF in order to
		// be able to use
		// generic regex expression (match \n for new lines)
		$markdown = str_replace("\r\n", "\n", $markdown);

		// Call the convert class so the markdown content will be
		// translated into HTML; by running all plugins and, also,
		// retrieving the HTML template for displaying the note.
		$params = array(
			'markdown'=>$markdown,
			'filename'=>$fullname);

		$aeTask = \MarkNotes\Tasks\Display::getInstance();
		$sHTML = $aeTask->run($params);

		// Now, remove, from the final HTML, a few things
		// like the navigation bar; which is not needed in the preview
		// pane in the editor
		$helpers = $aeSettings->getFolderAppRoot();
		$helpers .= 'marknotes/plugins/task/fetch/helpers/';
		$helpers = str_replace('/', DS, $helpers);

		if (is_file($helpers.'clean_html.php')) {

			require_once($helpers.'clean_html.php');

			$aeClean = new \MarkNotes\Plugins\Task\Fetch\Helpers\CleanHTML($sHTML, '');

			$arrRemoveDOM=array(
				"//footer","//link","//meta","//title"
			);

			$aeClean->setRemoveDOM($arrRemoveDOM);

			// Make some changes on the content like
			// converting "container" to "container-fluid"
			// so the HTML will use the full area and not only
			// a limited width
			// Also add a notice
			$arrRegex = array(
				[
					"search" => '~<div class="container">~i',
					"replace_by" =>
						'<div class="container-fluid">'.
						'<div class="alert alert-warning" role="alert"><ol>'.
						$aeSettings->getText('editor_preview','').
						'</ol></div>'
				]
			);

			$aeClean->setRegex($arrRegex);

			$sHTML = $aeClean->doIt();

			unset($aeClean);
		}

		$status = array(
			'status' => 1,
			'html' => $sHTML
		);

		header('Content-Type: application/json; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo json_encode($status, JSON_PRETTY_PRINT);

		return true;
	}
}
