<?php
/**
 * Convert the Markdown string into a HTML one, rely on
 * parsedown for this
 * @link https://github.com/erusev/parsedown
 */
namespace MarkNotes\Helpers;

defined('_MARKNOTES') or die('No direct access allowed');

class Convert
{
	protected static $hInstance = null;

	public function __construct()
	{
		return true;
	}

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Convert();
		}

		return self::$hInstance;
	}

	/**
	 *  Convert the Markdown string into a HTML one
	 */
	public function getHTML(string $markdown, array $params = null,
		bool $bRunContentPlugin = true) : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Call the Markdown parser
		$file = "Parsedown";
		$lib = "Parsedown";
		$folder = $aeSettings->getFolderLibs()."erusev/parsedown/";

		if (!$aeFiles->exists($folder.$file.'.php')) {
			self::ShowError(
				str_replace(
					'%s',
					'<strong>'.$folder.$file.'.php</strong>',
					$aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists')
				),
				true
			);
		}

		// --------------------
		$task = $params['task'] ?? '';
		if (!in_array($task, array('task.export.reveal','task.export.remark'))) {
			// When writing a note, the author can type, on a
			// new line, --- or ----- to ask marknotes to insert
			// a new slide break.

			$aeSettings = \MarkNotes\Settings::getInstance();
			$arrSettings = $aeSettings->getPlugins(JSON_OPTIONS_REGEX);
			$pageBreak = $arrSettings['page_break'] ?? '\n+^-{3,5}$\n+';

			$markdown = preg_replace('/('.$pageBreak.')/m', '', $markdown);
		}

		include_once $folder.$file.'.php';

		$aeFiles = \MarkNotes\Files::getInstance();
		if ($aeFiles->exists($extra = $folder.$file.'Extra'.'.php')) {
			$lib = "ParsedownExtra";
			include_once $extra;
		}

		$parsedown = new \ParsedownCheckbox();
		if ($aeFiles->exists($extra = $folder.'leblanc-simon/parsedown-checkbox/ParsedownCheckBox'.'.php')) {
			$lib = "ParsedownCheckbox";
			//include_once $extra;
		}

		if ($aeFiles->exists($extra = $aeSettings->getHelpersRoot().'markNotesParsedown.php')) {
			$lib = "marknotesParsedown";
			include_once $extra;
		}

		$parsedown = new $lib();

		$html = $parsedown->text(trim($markdown));

		// Add IDs to headings and paragraph, should be done
		// here for, f.i. the TOC plugin : headings should already
		// have an id to allow plugins to make their job correctly

		$aeHTML = \MarkNotes\FileType\HTML::getInstance();
		$html = $aeHTML->addHeadingsID($html, false);

		$html = $aeHTML->addParagraphsID($html);

		if ($bRunContentPlugin) {
			// --------------------------------
			// Call content plugins
			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->loadPlugins('content.html');
			$args = array(&$html);
			$aeEvents->trigger('content.html::render.content', $args);
			$html = $args[0];
		}

		return $html;
	}
}
