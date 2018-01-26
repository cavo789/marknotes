<?php
/**
* Gererate a fake index.html file i.e. return the
* list of files present in the folder; just like if
* an "index.html" was foresee
*
* Return a dynamic index.html page : the user will be
* able to address any folders just by typing the URL like
* http://site/docs/folder/subfolder and followed by index.html.
*
* That index.html file doesn't exists and will be generated
* (html rendering) by this class.
*
* The content of the /templates/index.php file will be
* taken (as template) and the list of .md files immediatly
* in the adressed folder (and not in subfolders) will
* be collected.
*
* This task will generate a <ul><li> list and append in
* into the template then return the html.
*
* Example : http://localhost/marknotes/docs/CMS/Joomla/index.html
* will display the list of .md notes of /docs/CMS/Joomla
*/
namespace MarkNotes\Plugins\Task\Index;

defined('_MARKNOTES') or die('No direct access allowed');

class GetIndex extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.index';
	protected static $json_options = 'plugins.options.task.index';

	/**
	* This function is called when the folder contains a file
	* called index.md i.e. when no index.html should be
	* "generated" but when the plugin just need to read the
	* index.md and convert it to html.
	*/
	private static function readIndexMD(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeMarkDown = \MarkNotes\Markdown::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$task = 'task.export.html';

		$filename = $aeFiles->removeExtension(ltrim($params['filename'])).'.md';

		$aeSession->set('task', $task);

		$params['filename'] = $filename;

		$aeMarkDown->process($task, $filename, $params);
		unset($aeMarkDown);

		return true;
	}

	private static function makeIndexHTML(&$params = null) : bool
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeMD = \MarkNotes\FileType\Markdown::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$fullname = $params['fullname'];
		$folder = dirname($fullname);

		// $fullname will be something like
		// c:\site\docs\docs\CMS\Joomla\index.html
		//
		// ==> the objective is to create an index.html rendering
		// with the list of .md Files found in the
		// c:\site\docs\docs\CMS\Joomla\ folder.

		if (!$aeFolders->exists($folder)) {
			$aeFunctions->folderNotFound($folder);
		}

		// Be sure the user can get access to this folder
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('task.acls.cansee');

		// Note : the folder should start and end with the slash
		$arr = array('folder' => $fullname,'return' => true);
		$args = array(&$arr);

		$aeEvents->trigger('task.acls.cansee::run', $args);

		// cansee will initialize return to 0 if the user can't
		// see the folder
		if (intval($args[0]['return'])===1) {
			$files = glob(mb_convert_encoding($folder, "ISO-8859-1", "UTF-8").DS."*.md");

			$arr=array();
			foreach ($files as $file) {
				$markdown = $aeFiles->getContent($file);

				$arr[] = array(
					'fmtime' => $aeFiles->timestamp($file),
					'time' => date("Y-m-d", $aeFiles->timestamp($file)),
					'file' => utf8_encode($aeFiles->removeExtension(basename($file))),
					'text' => $aeMD->getHeadingText($markdown)
				);
			}

			// Sort the list of files, descending (the most recent file first)
			usort($arr, function ($a, $b) {
				return strcmp($b['fmtime'], $a['fmtime']);
			});
		} else {
			$arr=array();
		} // if (intval($args[0]['return'])===1)

		// Retrieve the settings for the boostrap list items
		$aeSettings = \MarkNotes\Settings::getInstance();
		$arrSettings = $aeSettings->getPlugins('options.content.html.bootstrap');

		$icon = $arrSettings['bullet'] ?? 'check';
		$extra = $arrSettings['extra_attribute'] ?? '';

		// Build the list
		$list = '<ul class="fa-ul">';

		foreach ($arr as $entry) {
			$list .= '<li><i class="fa-li fa fa-'.$icon.'" '.$extra.'></i><span class="index_date">'.$entry['time'].' - </span><a href="'.$entry['file'].'.html" class="index_file">'.$entry['text'].'</a> '.
			'(<a href="'.$entry['file'].'.reveal" class="index_file">reveal</a>)</li>';
		}

		$list .= '</ul>';

		// Read the template
		$template = $aeFiles->getContent($aeSettings->getTemplateFile('index'));

		// And generate the output : template + list of files
		$html = str_replace('%CONTENT%', $list, $template);

		// --------------------------------
		//
		// Call the variables markdown plugin for variables's translation
		$params['markdown'] = $html;
		$aeEvents->loadPlugins('markdown.variables');
		$args = array(&$params);
		$aeEvents->trigger('markdown.variables::markdown.read', $args);
		$html = $args[0]['markdown'];

		$aeEvents->loadPlugins('content.html');
		$args = array(&$html);
		$aeEvents->trigger('content.html::render.content', $args);
		$html = $args[0];

		// --------------------------------
		// Call content page plugins just like if we're rendering an
		// HTML note

		// "Fake" : we're processing task.index.get but we need to
		// call page.html plugins so... let thinks we're processing
		// task.export.html
		$old_task=$aeSession->get('task');
		$aeSession->set('task', 'task.export.html');
		$aeEvents->loadPlugins('page.html');
		$args = array(&$html);
		$aeEvents->trigger('page.html::render.html', $args);
		$html = $args[0];

		$additionnalJS = '';
		$args = array(&$additionnalJS);
		$aeEvents->trigger('page.html::render.js', $args);
		$html = str_replace('<!--%ADDITIONNAL_JS%-->', $args[0], $html);

		$css = '';
		$args = array(&$css);
		$aeEvents->trigger('page.html::render.css', $args);
		$html = str_replace('<!--%ADDITIONNAL_CSS%-->', $args[0], $html);

		// Restore the original task (task.index.get)
		$aeSession->set('task', $old_task);

		echo $html;

		return true;
	}

	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$docRoot = str_replace(DS, '/', $aeSettings->getFolderDocs(false));

		// The filename shouldn't mention the docs folders, just the filename
		// So, $filename should not be docs/markdown.md but only markdown.md because the
		// folder name will be added later on
		if (substr($params['filename'], 0, strlen($docRoot)) === $docRoot) {
			$params['filename'] = substr($params['filename'], strlen($docRoot));
		}

		// If the filename doesn't mention the file's extension, add it.
		if (substr($params['filename'], -5) != '.html') {
			$params['filename'] .= '.html';
		}

		$fullname = ltrim($params['filename'], DS);
		$fullname = str_replace('/', DS, $aeSettings->getFolderDocs(true).$fullname);

		$params['fullname'] = $fullname;

		// Check if an index.md file exists in the folder, if yes,
		// read it and convert it into HTML
		$md = $aeFiles->removeExtension($fullname).'.md';
		if ($aeFiles->exists($md)) {
			self::readIndexMD($params);
		} else {
			self::makeIndexHTML($params);
		}

		return true;
	}
}
