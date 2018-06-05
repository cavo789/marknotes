<?php
/**
 * What are the actions to fired when MarkNotes is
 * running the "reveal" task ?
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
		$aeFiles = \MarkNotes\Files::getInstance();
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

		$template = $aeFiles->getContent($template);

		return $template;
	}

	private static function getHTML(array $params) : string
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$filename = $params['filename'];

		if ($aeFiles->getExtension($filename)==='reveal.pdf') {
			$filename = $aeFiles->removeExtension($filename);
		}

		// If the filename doesn't mention the file's
		// extension, add it.
		if (substr($filename, -3) != '.md') {
			$params['filename'] = $aeFiles->removeExtension($filename).'.md';
		}

		// Get the template to use
		$template = self::getTemplate($params);

		// Trigger render.js and render.css in order to
		// retrieve JS and CSS and put them in the template
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

		// $params['filename'] is f.i. "note.reveal", get
		// the .md file
		$fullname = $aeSettings->getFolderDocs(true).$params['filename'];
		$fullname = $aeFiles->removeExtension($fullname).'.md';

		// reveal can work both with HTML content or markdown
		// content. Check settings.json and take a look on the
		// no_html_convert option. If equal to 1, don't
		// convert the .md note into a html string
		$no_html_convert = boolval(self::getOptions('no_html_convert', 0));

		// Get the markdown content
		$aeEvents->loadPlugins('markdown');
		$content = $aeFiles->getContent($fullname);
		if (trim($content) == '') {
			$content = $aeFiles->getContent(utf8_decode($fullname));
		}

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

		return $html;
	}

	public static function run(&$params = null) : bool
	{
		$bReturn = true;

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_CACHE);
		$bCache = boolval($arrSettings['enabled'] ?? false);

		$html = '';

		$fullname = $aeFiles->makeFileNameAbsolute($params['filename']);

		if ($bCache) {
			$aeCache = \MarkNotes\Cache::getInstance();

			// Set the HTML of the note in the cache but prefixed by
			// the connected user name
			$key = $aeSession->getUser().'###'.$fullname;

			$cached = $aeCache->getItem(md5($key));
			$data = $cached->get();
			$html = $data['html']??'';
		}

		if (trim($html) == '') {

			$html = self::getHTML($params);

			if ($bCache) {
				// Save the list in the cache
				$arr = array();
				$arr['from_cache'] = 1;
				$arr['html'] = $html;
				// Get the duration for the HTML cache (default : 31 days)
				$duration = $arrSettings['duration']['html'];

				// Add a tag to the cached item : the fullname of the
				// note so we can kill with a
				// $aeCache->deleteItemsByTag(md5($fullname));
				// every cached items concerning this note
				$cached->set($arr)->expiresAfter($duration)->addTag(md5($key));
				$aeCache->save($cached);
				$arr['from_cache'] = 0;
			}
		} else { // if ($html == '')
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log("	Retrieved from cache [".$key."]","debug");
			}
			/*<!-- endbuild -->*/

			// Debug : add a meta cache=1 just after the <head> tag
			// Get the start position of the tag
			preg_match('~<head.*~', $html, $matches, PREG_OFFSET_CAPTURE);
			$pos = $matches[0][1];
			// Get the ">" character so we can know where <head> is
			// positionned since, perhaps, there are a few attributes
			$pos = strpos($html, '>', $pos) + 1;

			// Ok, insert the new meta
			$meta = '<meta name="cached" content="1">';
			$html = substr_replace($html, $meta, $pos, 0);
		}

		$params['content'] = $html;

		// Last thing : if the filename was with
		// an extension .reveal.pdf, we also need to generate
		// a .pdf file
		$filename = $params['filename'];
		$aeFiles = \MarkNotes\Files::getInstance();
		if ($aeFiles->getExtension($filename)=='reveal.pdf') {
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->loadPlugins('task.export.pdf');
			$args = array(&$params);
			$aeEvents->trigger('task.export.pdf::run', $args);
			unset($params['content']);
			$params['extension'] = 'pdf';
		}

		return true;
	}
}
