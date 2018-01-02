<?php
/**
 * Add a justified image gallery
 * @link http://miromannino.github.io/Justified-Gallery/
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Image_Gallery extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.image_gallery';
	protected static $json_options = 'plugins.options.page.html.image_gallery';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url.= '/marknotes/plugins/page/html/image_gallery/';

		$script =
			"\n<script type=\"text/javascript\" ". "src=\"".$url."libs/justified-gallery/jquery.justifiedGallery.min.js\" ".
			"defer=\"defer\"></script>\n".
			"<script type=\"text/javascript\" ". "src=\"".$url."image_gallery.js\" ".
			"defer=\"defer\">".
			"</script>\n";

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

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url.= '/marknotes/plugins/page/html/image_gallery/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". "href=\"".$url."libs/justified-gallery/justifiedGallery.min.css\">\n";

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

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = false;

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Open the .md file and check if the %GALLERY% tag is
		// there. If yes, then the plugin has something to do.
		// If not mentionned, it's not needed to add the .js and .css
		// file of this plugin since they don't have any things to do

		$filename = trim($aeSession->get('filename'));

		if ($filename=='') {
			return false;
		}

		$filename=$aeFiles->removeExtension($filename).'.md';
		$filename=$aeSettings->getFolderDocs(true).$filename;

		$content = '';

		//if ($aeFiles->exists($filename)) {
			$content = trim($aeFiles->getContent($filename));
		//} elseif ($aeFiles->exists(utf8_decode($filename))) {
			// Arrrgh, sometimes with sometimes without utf8_decode,
			// it's crazy
		//	$content = trim($aeFiles->getContent(utf8_decode($filename)));
		//}

		// Search the plugin's tag
		$pattern = '/%GALLERY ([^\\%]*)%/';

		if (preg_match_all($pattern, $content, $matches)) {
			// Ok, tag found => the plugin has added value
			$bCanRun = true;
		}

		return $bCanRun;
	}
}
