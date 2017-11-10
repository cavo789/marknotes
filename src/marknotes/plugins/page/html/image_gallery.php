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

		$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url.= '/marknotes/plugins/page/html/image_gallery/';

		$script =
			"\n<script type=\"text/javascript\"". "src=\"".$url."libs/justified-gallery/jquery.justifiedGallery.min.js\" ".
			"defer=\"defer\"></script>\n".
			"<script type=\"text/javascript\" src=\"".$url."image_gallery.js\" ".
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

		$url = rtrim($aeFunctions->getCurrentURL(true, false), '/');
		$url.= '/marknotes/plugins/page/html/image_gallery/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\"". "href=\"".$url."libs/justified-gallery/justifiedGallery.min.css\">\n";

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
