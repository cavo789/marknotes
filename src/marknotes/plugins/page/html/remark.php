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

		$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url .= '/marknotes/plugins/page/html/remark/';

		$task = $aeSession->get('task', '');

		if ($task==='task.export.remark') {
			$script = "<script type=\"text/javascript\" ".
				"src=\"".$url."libs/remark/remark.min.js\" ".
				"defer=\"defer\"></script>\n";

			$script .= "<script type=\"text/javascript\" ".
				"src=\"".$url."remark.js\" ".
				"defer=\"defer\"></script>\n";

			$arrSettings = $aeSettings->getPlugins(static::$json_key);

			// Get settings
			$minutes = intval($arrSettings['duration']['minutes']) ?? 60;
			$barHeight = intval($arrSettings['duration']['bar_height']) ?? 3;
			$hide = intval($arrSettings['HideUnnecessaryThings']) ?? 0;

			// Get the note URL
			$url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

			$filename = $aeSession->get('filename');
			$urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($filename, 'html'));

			$script .=
				"<script type=\"text/javascript\">\n".
				"marknotes.note = {};\n".
				"marknotes.note.url = '".$urlHTML."';\n".
				"marknotes.slideshow = {};\n".
				"marknotes.slideshow.durationMinutes=".$minutes.";\n".
				"marknotes.slideshow.durationBarHeight=".$barHeight.";\n".
				"marknotes.slideshow.hideunnecessarythings=".($hide ? 1 : 0).";\n".
				"</script>";
		} else {
			$script = "<script type=\"text/javascript\" ".
				"src=\"".$url."button.js\" ".
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
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrSettings = $aeSettings->getPlugins(static::$json_key);

		$appearance = $arrSettings['appearance'] ?? array('theme'=>'beige');

		$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url .= '/marknotes/plugins/page/html/reveal/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/css/reveal.css\">\n".
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/css/theme/".$appearance['theme'].".css\">\n".
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/lib/css/zenburn.css\">\n".
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/plugin/title-footer/title-footer.css\">\n";

		$css .= $aeFunctions->addStyleInline($script);

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
