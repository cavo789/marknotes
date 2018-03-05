<?php
/**
 * Load JS and CSS for remark.js
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Remark extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.remark';
	protected static $json_options = 'plugins.options.page.html.remark';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/remark/';

		$task = $aeSession->get('task', '');

		if ($task==='task.export.remark') {
			$script = "<script ".
				"src=\"".$url."libs/remark/remark.min.js\" ".
				"defer=\"defer\"></script>\n";

			$arrOptions = self::getOptions('duration', array('minutes'=>60, 'bar_height'=>3));

			// Get settings
			$minutes = intval($arrOptions['minutes']) ?? 60;
			$barHeight = intval($arrOptions['bar_height']) ?? 3;

			// Get the note URL
			$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

			$filename = $aeSession->get('filename');
			$urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($filename, 'html'));

			$script .=
				"<script>\n".
				"marknotes.note = {};\n".
				"marknotes.note.url = '".$urlHTML."';\n".
				"marknotes.slideshow = {};\n".
				"marknotes.slideshow.durationMinutes=".$minutes.";\n".
				"marknotes.slideshow.durationBarHeight=".$barHeight.";\n".
				"</script>";

		} else {
			$script = "<script src=\"".$url."button.js\" ".
				"defer=\"defer\"></script>";
		}

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		return true;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		return true;
	}
}
