<?php
/**
 * What are the actions to fired when MarkNotes is running the "reveal" task ?
 */

namespace MarkNotes\Plugins\Task\Export;

defined('_MARKNOTES') or die('No direct access allowed');

class Reveal extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.export.reveal';
	protected static $json_options = 'plugins.options.page.html.reveal';

	private static $extension = 'reveal';

	/**
	 * Retrieve the template for the presentation and use it
	 */
	private static function getTemplate(array $params = array()) : string
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeHTML = \MarkNotes\FileType\HTML::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$root = rtrim($aeFunctions->getCurrentURL(), '/');
		$template = $aeSettings->getTemplateFile(static::$extension);
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('Template used : '.$root.$template);
		}

		if ($template === '') {
			$aeFunctions->fileNotFound($template);
		}

		$template = file_get_contents($template);

		return $template;
	}

	public static function run(&$params = null) : bool
	{
		$bReturn = true;

		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// If the filename doesn't mention the file's extension, add it.
		if (substr($params['filename'], -3) != '.md') {
			$params['filename'] = $aeFiles->removeExtension($params['filename']).'.md';
		}

		// Get the template to use
		$template = self::getTemplate($params);

		// Trigger render.js and render.css in order to retrieve JS and CSS
		// and put them in the template
		$aeEvents = \MarkNotes\Events::getInstance();

		$aeEvents->loadPlugins('page.html');

		$additionnalJS = '';
		$args = array(&$additionnalJS);
		$aeEvents->trigger('page.html::render.js', $args);
		$template = str_replace('<!--%ADDITIONNAL_JS%-->', $args[0], $template);

		$additionnalCSS = '';
		$args = array(&$additionnalCSS);
		$aeEvents->trigger('page.html::render.css', $args);
		$template = str_replace('<!--%ADDITIONNAL_CSS%-->', $args[0], $template);

		// Now, get the content

		// $params['filename'] is f.i. "note.reveal", get the .md file
		$fullname = $aeSettings->getFolderDocs(true).$params['filename'];
		$fullname = $aeFiles->removeExtension($fullname).'.md';

		// reveal can work both with HTML content or markdown content.
		// Check settings.json and take a look on the no_html_convert option
		// If equal to 1, don't convert the .md note into a html string
		$no_html_convert = boolval(self::getOptions('no_html_convert', 0));

		// Get the markdown content
		$aeEvents->loadPlugins('markdown');
		$content = file_get_contents($fullname);
		$params['markdown'] = $content;
		$params['filename'] = $fullname;
		$args = array(&$params);
		$aeEvents->trigger('markdown::markdown.read', $args);
		$content = $args[0]['markdown'];

		if (!$no_html_convert) {
			// Convert markdown to HTML first
			$aeConvert = \MarkNotes\Helpers\Convert::getInstance();
			$content = $aeConvert->getHTML($content, $params, true);
		}

		// Run the reveal content plugin
		$aeEvents->loadPlugins('content.slides.reveal');
		$arr=array('html'=>$content);
		$args = array(&$arr);
		$aeEvents->trigger('content.slides.reveal::run', $args);
		$content = $args[0]['html'];

		$aeHTML = \MarkNotes\FileType\HTML::getInstance();
		$html = $aeHTML->replaceVariables($template, $content, $params);

		$final = $aeFiles->removeExtension($params['filename']).'.'.static::$extension;

		if (!$aeFunctions->startsWith($final, $aeSettings->getFolderDocs(true))) {
			$final = $aeSettings->getFolderDocs(true).$final;
		}

		// Generate the file ... only if not yet there
		if (!$aeFiles->fileExists($final)) {
			// Display the HTML rendering of a note
			//$aeTask = \MarkNotes\Tasks\Display::getInstance();

			// Get the HTML content
			//$content = $params['html']; //$aeTask->run($params);

			if (!$aeFiles->createFile($final, $html)) {
				$final = '';

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("Error while trying to create [".$final."]", "error");
				}
				/*<!-- endbuild -->*/
			}
		}  // 	if(!$aeFiles->fileExists($final))

		$params['output'] = $final;

		return true;
	}
}
