<?php
/**
 * Return a timeline with the list of notes displayed in a descending
 * chronological order
 */
namespace MarkNotes\Plugins\Task\Timeline;

defined('_MARKNOTES') or die('No direct access allowed');

class Get extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.timeline';
	protected static $json_options = 'plugins.options.task.timeline';

	/**
	 * Get the list of notes, relies on the listFiles task plugin for this
	 * in order to, among other things, be sure that only files that the
	 * user can access are retrieved and not confidential ones
	 */
	private static function getFiles() : array
	{
		$arrFiles = array();

		// Call the listfiles.get event and initialize $arrFiles
		$aeEvents = \MarkNotes\Events::getInstance();
		$args=array(&$arrFiles);
		$aeEvents->loadPlugins('task.listfiles.get');
		$aeEvents->trigger('task.listfiles.get::run', $args);

		return $args[0];
	}

	private static function getJSON() : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeJSON = \MarkNotes\JSON::getInstance();
		$aeMarkDown = \MarkNotes\FileType\MarkDown::getInstance();

		$sReturn = '';

		$arrOptimize = $aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);
		$bOptimize = $arrOptimize['server_session'] ?? false;
		if ($bOptimize) {
			// Get the list of files/folders from the session object if possible
			$aeSession = \MarkNotes\Session::getInstance();
			$sReturn = $aeSession->get('timeline', '');
		}

		if ($sReturn === '') {
			$json = array();

			$folder = str_replace('/', DS, $aeSettings->getFolderDocs(true));

			$arrFiles = self::getFiles();

			// -------------------------------------------------------
			// Based on https://github.com/Albejr/jquery-albe-timeline
			// -------------------------------------------------------

			foreach ($arrFiles as $file) {
				// Calling $aeMarkDown->read will, also, fire markdown::read
				// event and thus plugins. This for every files in $arrFiles
				// ==> can be really slow.
				//$content = $aeMarkDown->read($file);

				// Optimization : just read file on disk, without plugin support
				$content = file_get_contents($file);

				$relFileName = utf8_encode(str_replace($folder, '', $file));

				$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DIRECTORY_SEPARATOR).'/';
				$urlHTML = $url.str_replace(DIRECTORY_SEPARATOR, '/', $aeFiles->replaceExtension($relFileName, 'html'));

				$json[] =
				  array(
				'fmtime' => filectime($file),
				'time' => date("Y-m-d", filectime($file)),
				'header' => htmlentities($aeMarkDown->getHeadingText($content)),
				'body' => array(
				  array(
					'tag' => 'a',
					'content' => $relFileName,
					'attr' => array(
					  'href' => $urlHTML,
					  'target' => '_blank',
					  'title' => $relFileName
					) // attr
				  ),
				  array(
					'tag' => 'span',
					'content' => ' ('
				  ),
				  array(
				  'tag' => 'a',
				  'content' => 'slide',
				  'attr' => array(
					'href' => $urlHTML.'?format=slides',
					'target' => '_blank',
					'title' => $relFileName
					) // attr
				  ),
				  array(
					'tag' => 'span',
					'content' => ' - '
				  ),
				  array(
				  'tag' => 'a',
				  'content' => 'pdf',
				  'attr' => array(
					'href' => $urlHTML.'?format=pdf',
					'target' => '_blank',
					'title' => $relFileName
					) // attr
				  ),
				  array(
					'tag' => 'span',
					'content' => ')'
				  )
				  ) // body
				); //
			} // foreach

			usort($json, function ($a, $b) {
				//return strtotime($a['start_date']) - strtotime($b['start_date']);
				return strcmp($b['fmtime'], $a['fmtime']);
			});

			$sReturn = $aeJSON->json_encode($json, JSON_PRETTY_PRINT);

			if ($bOptimize) {
				// Remember for the next call
				$aeSession->set('timeline', $sReturn);
			}
		}

		header('Content-Type: application/json');
		echo $sReturn;

		return true;
	}

	private static function getHTML(array $params = array()) : bool
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeHTML = \MarkNotes\FileType\HTML::getInstance();
		$arrOptimize = $aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);
		$bOptimize = $arrOptimize['localStorage'] ?? false;

		$template = file_get_contents($aeSettings->getTemplateFile('timeline'));

		// The template can contains variables so call the variables
		// plugins to translate them
		// (the markdown.variables can be called even if, here, the
		// content is a HTML string)
		$aeEvents->loadPlugins('markdown.variables');
		$tmp = array('markdown'=>$template, 'filename'=>$params['filename']);
		$args = array(&$tmp);
		$aeEvents->trigger('markdown.variables::markdown.read', $args);
		$template = $args[0]['markdown'];

		$aeEvents->loadPlugins('page.html');
		$args = array(&$template);
		$aeEvents->trigger('page.html::render.html', $args);
		$template = $args[0];

		$additionnalJS = '';
		$args = array(&$additionnalJS);
		$aeEvents->trigger('page.html::render.js', $args);
		$template = str_replace('<!--%ADDITIONNAL_JS%-->', $args[0], $template);

		$additionnalCS = '';
		$args = array(&$additionnalCS);
		$aeEvents->trigger('page.html::render.css', $args);
		$template = str_replace('<!--%ADDITIONNAL_CSS%-->', $args[0], $template);

		header('Content-Type: text/html; charset=utf-8');
		echo $aeHTML->replaceVariables($template, '', $params);

		return true;
	}

	public static function run(&$params = null) : bool
	{

		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// filename will be timeline.html or timeline.json, keep only the extension
		$layout = trim($aeSession->get('filename', ''));

		$layout = substr($layout, -4);

		if ($layout === 'html') {
			self::getHTML($params);
		} else {
			self::getJSON();
		}

		return true;
	}
}
