<?php
/**
 * Fake loading
 * @link https://github.com/joaopereirawd/fakeLoader.js
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class FakeLoader extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.fakeLoader';
	protected static $json_options = 'plugins.options.page.html.fakeLoader';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$url .= 'marknotes/plugins/page/html/fakeLoader/';

		$script = "<script type=\"text/javascript\" ".
			" src=\"".$url."libs/fakeLoader.js/fakeLoader.js\"></script>\n".
			"<script type=\"text/javascript\">\n".
			"marknotes.fakeLoader={};\n".
			"marknotes.fakeLoader.bgColor='".self::getOptions('bgColor', '#2ecc71')."';\n".
			"marknotes.fakeLoader.spinner='".self::getOptions('spinner', 'spinner1')."';\n".
			"marknotes.fakeLoader.timeToHide='".self::getOptions('timeToHide', '#5000')."';\n".
			"</script>\n".
			"<script type=\"text/javascript\" ".
			"src=\"".$url."fakeLoader.js\"></script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$url .= 'marknotes/plugins/page/html/fakeLoader/';

		$script = "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ". 	"href=\"".$url."libs/fakeLoader.js/fakeLoader.css\" />\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Verify if the plugin is well needed and thus have a reason
	 * to be fired
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// fakeLoader is only for esthetics puroposes so don't load it
			// when the site is visited by a bot (googlebot, bing, baidu, ...)

			// @link https://github.com/JayBizzle/Crawler-Detect
			$CrawlerDetect = new \Jaybizzle\CrawlerDetect\CrawlerDetect;

			// Check the user agent of the current 'visitor'
			$bCanRun = !$CrawlerDetect->isCrawler();

			unset($CrawlerDetect);
		}

		return $bCanRun;
	}

	/**
	 * Add/modify the HTML content
	 */
	public static function doIt(&$html = null) : bool
	{
		return true;
	}
}
