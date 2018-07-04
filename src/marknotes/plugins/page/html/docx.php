<?php
/**
 * The conversion to a .docx file is made by Pandoc but only if Pandoc is
 * install so, to be able to offer a fallback solution, the conversion will
 * also rely on Googoose as soon as Pandoc isn't found on the localsystem.
 * @link https://github.com/aadel112/googoose
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Docx extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.docx';
	protected static $json_options = '';

	private static $layout = 'docx';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/docx/';

		$script =
			"<script src=\"".$url."button.js\" ".
			"defer=\"defer\"></script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}
	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
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

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();
		return $bCanRun;
	}
}
