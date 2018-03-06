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

		// ----------------------------------------------
		// Detect if the file a .reveal or .reveal.pdf
		// This is needed because some dependencies like the
		// js-menu should not be loaded if the layout is a pdf
		// file but well (if enabled) when it's a .reveal online
		// presentation
		$filename = trim($aeSession->get('filename', ''));

		$bPDF = false;
		if ($filename !== '') {
			// Don't run add_icons for PDF exportation
			// (will give errors with decktape)
			$ext = $aeFiles->getExtension($filename);
			$bPDF = in_array($ext, array('pdf', 'reveal.pdf', 'remark.pdf'));
		}
		//
		// ----------------------------------------------

		$script = "";

		if ($task==='task.export.reveal') {

			// Initialize the list of dependencies
			// This variable will be received the .js script
			// of any plugins that should be loaded
			$dependencies='';

			// Only when, indeed we're showing a reveal slideshow

			// --------------------------------------------
			// Get the list of templates available
			// This list of templates will be accessible in the
			// reveal.js-menu, at the bottom left during the
			// slideshow
			$dir = __DIR__.'/reveal/libs/reveal.js/css/theme/';

			$arrThemes = glob($dir.'*.css');
			// Keep only the filename (f.i. beige.css)
			$arrThemes = array_map("basename", $arrThemes);

			$themes = '';

			foreach ($arrThemes as $file) {
				$themes .= str_replace('.css', '', $file).',';
			}

			$themes = rtrim($themes, ',');
			//
			// --------------------------------------------

			$script .= "<script ".
				"src=\"".$url."libs/reveal.js/js/reveal.js\" ".
				"defer=\"defer\"></script>\n".
			$script = "<script ".
				"src=\"".$url."libs/reveal.js/lib/js/head.min.js\" ".
				"defer=\"defer\"></script>\n";
			$script .= "<script src=\"".$url."reveal.js\" ".
				"defer=\"defer\"></script>";
		}

		// The button should be loaded also when task isn't
		// task.export.reveal
		$script .= "<script src=\"".$url."button.js\" ".
			"defer=\"defer\"></script>\n";

		if ($task==='task.export.reveal') {
			// Only when, indeed we're showing a reveal slideshow

			$arrOptions = self::getOptions('duration', array('enabled'=>1, 'minutes'=>60, 'bar_height'=>3));

			// Load the plugin only when enabled and only
			// if not rendering a PDF file
			if ((!$bPDF) && (boolval($arrOptions['enabled']))) {
				$dependencies.='plugin/elapsed-time-bar/elapsed-time-bar.js,';
			}

			$minutes = intval($arrOptions['minutes']) ?? 60;
			$barHeight = intval($arrOptions['bar_height']) ?? 3;

			$hide = intval(self::getOptions('HideUnnecessaryThings', 0));

			// Load the js-menu lib or not ?
			// It's the Hamburger menu at the bottom left
			$arrOptions = self::getOptions('menu', array('enabled'=>1));

			// Load the plugin only when enabled and only
			// if not rendering a PDF file
			if ((!$bPDF) && (boolval($arrOptions['enabled']))) {
				$dependencies.='plugin/reveal.js-menu/menu.js,';
			}

			// Load the speaker notes plugin or not
			$arrOptions = self::getOptions('speaker_notes', array('enabled'=>1));

			if (boolval($arrOptions['enabled'])) {
				$dependencies.='plugin/notes/notes.js,';
			}
			// Get the note URL
			$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

			$filename = $aeSession->get('filename');
			$urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($filename, 'html'));

			// Verify if Pandoc is installed and if so, add a
			// javascript variable to indicate this
			$aeConvert = \MarkNotes\Tasks\Convert::getInstance($aeSession->get('filename'), 'txt', 'pandoc');

			$bPandoc = $aeConvert->isValid();

			$script .=
				"<script>\n".
				"marknotes.note = {};\n".
				"marknotes.note.url = '".$urlHTML."';\n".
				"marknotes.note.url_noext = '".$aeFiles->removeExtension($urlHTML)."';\n".
				"marknotes.slideshow = {};\n".
				"marknotes.slideshow.durationMinutes=".$minutes.";\n".
				"marknotes.slideshow.durationBarHeight=".$barHeight.";\n".
				"marknotes.slideshow.hideunnecessarythings=".($hide ? 1 : 0).";\n".
				"marknotes.slideshow.pandoc=".($bPandoc ? 1 : 0).";\n".
				"marknotes.slideshow.dependencies='".rtrim($dependencies, ',')."';\n".
				"marknotes.slideshow.themes='".$themes."';\n".
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
			$script.="<script>\n".$tmp."</script>\n";
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

			$appearance = $arrOptions['theme'];

			$url = rtrim($aeFunctions->getCurrentURL(), '/');
			$url .= '/marknotes/plugins/page/html/reveal/';

			$script =
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/css/reveal.css\">\n".
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/css/theme/".$appearance.".css\">\n".
				"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/lib/css/zenburn.css\">\n";

				//"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$url."libs/reveal.js/plugin/title-footer/title-footer.css\">\n";

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
