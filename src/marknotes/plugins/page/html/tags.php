<?php
/**
 * Add hyperlinks to specific words (tags) so the reader can jump
 * between notes
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Tags extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.tags';
	protected static $json_options = JSON_OPTIONS_TAGS;

	/**
	 * Provide additionnal css
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$root = rtrim($aeFunctions->getCurrentURL(), '/');

		$script = "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/marknotes/plugins/page/html/tags/tags.css\" />\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeSession  = \MarkNotes\Session::getInstance();
		$task = $aeSession->get('task', '');

		// The tags.js, in October 2017, has the purpose to get the
		// clicked tag and to put it into the search engine of the
		// interface and to search notes for that tag.
		//
		// Only needed when the note is displayed through the interface
		if (in_array($task, array('main', 'interface'))) {
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$root = rtrim($aeFunctions->getCurrentURL(), '/');

			$script = "<script src=\"".$root."/marknotes/plugins/page/html/tags/tags.js\"></script>\n";

			$js .= $aeFunctions->addJavascriptInline($script);
		}

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
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// This plugin is only needed when at least one tag is mentionned
			$arrOptions = self::getOptions('keywords', array());
			$bCanRun = (count($arrOptions) > 0);
		}

		return $bCanRun;
	}
}
