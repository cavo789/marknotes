<?php
/**
 * Analyze each URLs in the note and add icons before when, f.i., the link
 * points to github, youtube, facebook, ... or to a file (.docx, .ppt, ...)
 *
 * Patterns are stored in settings.json like this :
 *
 *	"plugins": {
 *		"options": {
 *			"page": {
 *				"html": {
 *					"add_icons": {
 *						"position": "after",
 *						"regex": [
 * 							{
 *								 "pattern": ".*(github\\.com)\\/.*",
 *								 "icon": "fa-github"
 *							},
 * 							{
 *								 "pattern": ".*(youtube\\.)\\/.*",
 *								 "icon": "fa-youtube"
 *							},
 * 							{
 * 								 "pattern": "(\\.docx?)",
 * 								 "icon": "fa fa-file-word-o"
 * 							},
 *	 						{
 * 								 "pattern": "(\\.pdf)",
 * 								 "icon": "fa fa-file-pdf-o"
 * 							 }
 *						]
 *	 				}
 *				}
 *			}
 *		}
 *	}
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Add_Icons extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.add_icons';
	protected static $json_options = 'plugins.options.page.html.add_icons';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/').'/';
		$url .= 'marknotes/plugins/page/html/add_icons/';

		$position=strtolower(self::getOptions('position', 'before'));
		if (!in_array($position, array('after','before'))) {
			$position='before';
		}

		$arrOptions = self::getOptions('regex', array());

		$script="<script type=\"text/javascript\">".
			"var position_add_icons='".$position."';\n".
			"var json_add_icons=".json_encode($arrOptions).";\n".
			"</script>";

		$script .= "<script type=\"text/javascript\" src=\"".$url."add_icons.js\" ".
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
	 * Verify if the plugin is well needed and thus have a reason
	 * to be fired
	 */
	final protected static function canRun() : bool
	{

		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// Get plugin's options
			$arrOptions = self::getOptions('regex', array());
			if ($arrOptions !== array()) {
				$bCanRun = true;
			}
		}

		return $bCanRun;
	}
}
