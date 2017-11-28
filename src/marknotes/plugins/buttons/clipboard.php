<?php
/**
 * Add a Copy in the clipboard button into the toolbar
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class Clipboard extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.buttons.clipboard';
	protected static $json_linked = 'plugins.page.html.clipboard';

	public static function add(&$buttons = array()) : bool
	{
		/*
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(), '/');

		$title = $aeSettings->getText('copy_clipboard', 'Copy the note&#39;s content, with page layout, in the clipboard', true);

		$titlelink = $aeSettings->getText('copy_link', 'Copy the link to this note in the clipboard', true);

		$file = $aeSession->get('filename');
*/
		//$data = 'index.php?task=export.html&param='.base64_encode($file);

		// Get the button HTML code
		$buttons['clipboard'][] = self::button(
			array(
				'name' => 'clipboard',
				'title' => 'copy_clipboard',
				'default' => 'Copy the note&#39;s content, with page layout, in the clipboard',
				'task' => 'fnPluginButtonClipboard',
				'extra' => 'data-clipboard-action="copy" data-clipboard-target="#CONTENT"',
				'id' => 'icon_clipboard',
				'icon' => 'clipboard'
			)
		);

		$buttons['clipboard'][] = self::button(
			array(
				'title' => 'copy_link',
				'default' => 'Copy the link to this note in the clipboard',
				'task' => 'fnPluginButtonClipboardLinkNote',
				'id' => 'icon_link_note',
				'icon' => 'link'
			)
		);

		//$buttons .=
			//'<a id="icon_clipboard" data-task="fnPluginButtonClipboard" '.
			//	'data-clipboard-action="copy" data-clipboard-target="#CONTENT" '.
			//	'title="'.$title.'" href="#">'.
			//	'<i class="fa fa-clipboard debug" aria-hidden="true"></i>'.
			//'</a>'.
			//'<a id="icon_link_note" data-task="fnPluginButtonClipboardLinkNote" '.
			//	'title="'.$titlelink.'" href="#">'.
			//	'<i class="fa fa-link debug" aria-hidden="true"></i>'.
			//'</a>';

		return true;
	}
}
