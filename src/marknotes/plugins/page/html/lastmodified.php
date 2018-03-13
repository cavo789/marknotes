<?php
/**
 * LastModified
 * Load JS / CSS for the lastmodified task plugin
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class LastModified extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.lastmodified';
	protected static $json_options = 'plugins.options.task.lastmodified';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$url .= 'marknotes/plugins/page/html/lastmodified/';

		$script = "<script ".
			"src=\"".$url."lastmodified.js\"></script>\n";

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
		$url .= '/marknotes/plugins/page/html/lastmodified/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$url."lastmodified.css\">\n";

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
			// Verify that the task.lastmodified is well enabled
			$aeSettings = \MarkNotes\Settings::getInstance();
			$arr = $aeSettings->getPlugins('plugins.task.lastmodified', array('enabled'=>0));

			$bCanRun = boolval($arr['enabled']??0);
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
