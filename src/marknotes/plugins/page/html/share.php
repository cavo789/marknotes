<?php
/**
 * Provide sharing functionnalities
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Share extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.share';
	protected static $json_options = '';

	public static function doIt(&$html = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(false, false), '/');
		$urlHTML = '';
		if (isset($_REQUEST['file'])) {
			$urlHTML = $url.'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
			$urlHTML .= str_replace(DS, '/', $aeFiles->replaceExtension($_REQUEST['file'], 'html'));
		}

		if (file_exists($fname = __DIR__.'/share/template.html')) {
			$tmpl = str_replace('%URL%', $urlHTML, file_get_contents($fname));
			$tmpl = str_replace('%ROOT%', $url, $tmpl);
			$html = str_replace('</article>', '</article>'.$tmpl, $html);
		}

		return true;
	}

	/**
	 * Provide additionnal css
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". "href=\"".$root."/libs/jquery-toolbar/jquery.toolbar.css\" />\n".
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". "href=\"".$root."/marknotes/plugins/page/html/share/assets/share.css\" />\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

		$script = "<script type=\"text/javascript\" ". "src=\"".$root."/libs/jquery-toolbar/jquery.toolbar.min.js\" ".
		"defer=\"defer\"></script>\n".
		"<script type=\"text/javascript\" ". "src=\"".$root."/marknotes/plugins/page/html/share/assets/share.js\" ".
		"defer=\"defer\"></script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}
}
