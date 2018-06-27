<?php
/**
 * Edit form - Handle the Preview action
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

		$markdown = json_decode(urldecode($aeFunctions->getParam('markdown', 'string', '', true)));

		// Be sure to have content with LF and not CRLF in order to
		// be able to use
		// generic regex expression (match \n for new lines)
		$markdown = str_replace("\r\n", "\n", $markdown);

		$aeConvert = \MarkNotes\Helpers\Convert::getInstance();
		$html = $aeConvert->getHTML($markdown, array(), true);

/*<!-- build:debug -->*/
echo ("<pre style='background-color:yellow;'>".__FILE__." - ".__LINE__.PHP_EOL.str_replace('<','&lt;',trim($html))."</pre>");
/*<!-- build:debug -->*/
$aeDebug = \MarkNotes\Debug::getInstance();
$aeDebug->here("LIRE LE TEMPLATE ET RETOURNER UN HTML CORRECT", 1);
/*<!-- endbuild -->*/
die();
/*<!-- endbuild -->*/

		$status = array('status' => 1,'html' => $html);

		header('Content-Type: application/json; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo json_encode($status, JSON_PRETTY_PRINT);

		return true;
	}
}
