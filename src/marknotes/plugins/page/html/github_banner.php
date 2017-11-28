<?php
/**
 * Add a GitHub Fork to the page
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class github_banner extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.github_banner';
	protected static $json_options = '';

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$url .= 'marknotes/plugins/page/html/github_banner/';

		$script = "<link media=\"screen\" rel=\"stylesheet\" ".
			"type=\"text/css\" ". 	"href=\"".$url."libs/github-corners/styles.css\" />\n".
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". 	"href=\"".$url."github_banner.css\" />\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		return true;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$svg = __DIR__.'/github_banner/github-corner.svg';

		if (is_file($svg)) {
			$svg = file_get_contents($svg);

			$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
			$url .= 'marknotes/plugins/page/html/github_banner/';

			$link='<a href="'.$aeSettings->getPlugins('/github')['url'].'" '.
				'class="github-corner" aria-label="Fork on Github"></a>'.
				$svg;

			$html = str_replace('</article>', '</article>'.$link, $html);
		}
		return true;
	}
}
