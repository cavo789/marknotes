<?php
/**
 * Favorites
 * Load JS / CSS for the favorites task plugin
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Favorites extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.favorites';
	protected static $json_options = 'plugins.options.task.favorites';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$url .= 'marknotes/plugins/page/html/favorites/';

		// Count the number of items in the list of Favorites
		// If the plugin is enabled and when the list isn't empty
		// then this plugin can be fired
		$aeSettings = \MarkNotes\Settings::getInstance();

		// The plugin is enabled, check if the list of
		// favorites is empty of not
		$arr = $aeSettings->getPlugins(self::$json_options, array('list'=>array()));

		$script = "<script>".
			"marknotes.favorites_count=".count($arr['list']).";".
			"</script>\n";

		$script .= "<script ".
			"src=\"".$url."favorites.js\"></script>\n";

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
		$url .= '/marknotes/plugins/page/html/favorites/';

		$script =
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$url."favorites.css\">\n";

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
	 * Verify if the plugin is well needed and thus have a reason
	 * to be fired
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			$bCanRun = self::isEnabled(true);
		}

		return $bCanRun;
	}
}
