<?php
/**
 * Load JS and CSS for reveal.js
 */
namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Reveal extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.reveal';
	protected static $json_options = 'plugins.options.page.html.reveal';

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/reveal/';

		$task = $aeSession->get('task', '');

		$script = "";

		if ($task==='task.export.reveal') {
			// We're playing the slideshow, add reveal.js
			$script .= "<script type=\"text/javascript\" ".
				"src=\"".$url."libs/reveal.js/js/reveal.js\" ".
				"defer=\"defer\"></script>\n".
			$script = "<script type=\"text/javascript\" ".
				"src=\"".$url."libs/reveal.js/lib/js/head.min.js\" ".
				"defer=\"defer\"></script>\n";
			$script .= "<script type=\"text/javascript\" ".
				"src=\"".$url."reveal.js\" ".
				"defer=\"defer\"></script>";
		}

		$script .= "<script type=\"text/javascript\" ".
			"src=\"".$url."button.js\" ".
			"defer=\"defer\"></script>\n";

		if ($task==='task.export.reveal') {
			$arrOptions = self::getOptions('duration', array('minutes'=>60, 'bar_height'=>3));

			$minutes = intval($arrOptions['minutes']) ?? 60;
			$barHeight = intval($arrOptions['bar_height']) ?? 3;

			$hide = intval(self::getOptions('HideUnnecessaryThings', 0));

			// Get the note URL
			$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

			$filename = $aeSession->get('filename');
			$urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($filename, 'html'));

			$script .=
				"<script type=\"text/javascript\">\n".
				"marknotes.note = {};\n".
				"marknotes.note.url = '".$urlHTML."';\n".
				"marknotes.slideshow = {};\n".
				"marknotes.slideshow.durationMinutes=".$minutes.";\n".
				"marknotes.slideshow.durationBarHeight=".$barHeight.";\n".
				"marknotes.slideshow.hideunnecessarythings=".($hide ? 1 : 0).";\n".
				"</script>";
		}

		/**
		  * Check if there is a custom-js property in the options
		  *
		  * In settings.json, the user can have things like :
		  *
		  *		"reveal": {
		  *			"appearance": {
		  *				"custom": {
		  *					"js": [
		  *						"alert('Welcome in my super nice presentation);"
		  *					]
		  *				}
		  *			}
		  *		}
		  *
		  * As you can see, settings.json allow to add inline js, the
		  * code below will handle this.
		  */
		$arrOptions = self::getOptions('appearance.custom.js', array());

		if ($arrOptions!==array()) {
			// We've custom CSS, use it
			$tmp = "";
			foreach ($arrOptions as $line) {
				$tmp .= $line."\n";
			}

			// Add inline JS
			$script.="<script type=\"text/javascript\">\n".$tmp."</script>\n";
		}

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null) : bool
	{
		$aeSession = \MarkNotes\Session::getInstance();
		$task = $aeSession->get('task', '');

		if ($task==='task.export.reveal') {
			// Add CSS only when the slideshow is active

			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			//$arrSettings = $aeSettings->getPlugins(static::$json_key);
			$arrOptions = self::getOptions('appearance', array('theme'=>'beige'));
			$appearance = $arrOptions['theme']; // ?? array('theme'=>'beige');

			$url = rtrim($aeFunctions->getCurrentURL(), '/');
			$url .= '/marknotes/plugins/page/html/reveal/';

			$script =
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/css/reveal.css\">\n".
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/css/theme/".$appearance.".css\">\n".
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/lib/css/zenburn.css\">\n".
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/plugin/title-footer/title-footer.css\">\n";

			/**
			  * Check if there is a custom-css property in the options
			  *
			  * In settings.json, the user can have things like :
			  *
			  *		"reveal": {
			  *			"appearance": {
			  *				"theme": "beige",
			  *				"custom": {
			  *					"css": [
			  *						".reveal { font-size: 30px; }",
			  *						"section { background-color: white; }"
			  *					]
			  *				}
			  *			}
			  *		}
			  *
			  * As you can see, settings.json allow to add inline css, the
			  * code below will handle this.
			  */
			$arrOptions = self::getOptions('appearance.custom.css', array());

			if ($arrOptions!==array()) {
				// We've custom CSS, use it
				$tmp = "";
				foreach ($arrOptions as $line) {
					$tmp .= $line."\n";
				}

				// Add inline CSS
				$script.="<style media=\"screen\" type=\"text/css\">\n".$tmp."</style>\n";
			}


			$css .= $aeFunctions->addStyleInline($script);
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


}
