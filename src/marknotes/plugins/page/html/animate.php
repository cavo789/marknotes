<?php
/**
 * Add animate.css to the page
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Animate extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.animate';
	protected static $json_options = 'plugins.options.page.html.animate';

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeSession = \MarkNotes\Session::getInstance();
		// Not sure that the HTML has been stored in the session
		$html = $aeSession->get('html', '');

		// animate.css use a class called "animated".
		// If that word is found in the HTML, add animate.css
		// (perhaps a false positive) but, when the "animated" word
		// is not found, then, there, we know that animate.css
		// is not used at all so don't load the css in that case
		if (stripos($html, 'animated') !== false) {

			$aeFunctions = \MarkNotes\Functions::getInstance();

			$root = rtrim($aeFunctions->getCurrentURL(), '/');

			$script =
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
				"href=\"".$root."/libs/animate.css/animate.min.css\">\n";

			$css .= $aeFunctions->addStyleInline($script);
		}

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
		return true;
	}
}
