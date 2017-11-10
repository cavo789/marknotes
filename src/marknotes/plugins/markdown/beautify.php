<?php
/**
 * Process the markdown content, remove incorrect encoding,
 * be sure content is with LF (Unix style) and not CRLF (Windows style),
 * ...
 *
 * Make the markdown code proper by replacing "invalid" characters by
 * the correct ones.
 *
 * 1. Replace one of these characters by ...
 *
 *    Search      Replace by
 *    ------      ----------
 *    “           `
 *    ”           `
 *
 * 2. replace non breaking spaces introduced by Pandoc when the .md file
 *    is the result of a conversion from .docx to .md
 * 3. replace CRLF (Windows) by LF (Unix)
 * 4. replace empty lines (or lines with only spaces) by one single empty lines
 * 5. remove unneeded spaces between ### and the title (like in "###    My title")
 * x. replace links to image with absolute filename by the %URL% variable
 * x. replace links to the folder where the note resides by the %NOTE_FOLDER% variable
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Beautify extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.beautify';
	protected static $json_options = 'plugins.options.markdown.beautify';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		// Remember the original
		$markdown = $params['markdown'];

		// First action : remove unneeded spaces
		$markdown = trim($markdown);

		// Get options for the Beautify plugin
		$params['options']['remove_html_comments'] = self::getOptions('remove_html_comments', 1);
		$params['options']['rewrite_file'] = self::getOptions('rewrite_file', 0);

		// -------------------------------------------------
		// In order to keep this file has light as possible,
		// use external actions. The code below will get the list
		// of .php files in the folder beautify_actions and then
		// load and run each file, ony by one. No priority needed
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// Look in the beautify_actions subfolder and take every .php file
		$folder = __DIR__.'/beautify_actions/';
		$arrActions = $aeFiles->rglob($pattern = '*.php', $path = $folder);

		// Convention : if the filename start with an underscore
		// (like in _addThings.php)
		// consider that file as disabled so don't call it
		for ($i = count($arrActions); $i>0; $i--) {
			$action = $arrActions[$i-1];
			if (substr(basename($action), 0, 1) === '_') {
				unset($arrActions[$i-1]);
			}
		} // for($i

		// Run each actions sequentially
		if (count($arrActions)>0) {
			foreach ($arrActions as $action) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug = \MarkNotes\Debug::getInstance();
					$aeDebug->log("   Load [".$action."]", "debug");
				}
				/*<!-- endbuild -->*/

				try {
					require_once($action);

					// If the file is "images.php", the class will be "images"
					$class = $aeFiles->removeExtension(basename($action));

					$class = "\\MarkNotes\\Plugins\\Markdown\\Beautify_Actions\\".$class;

					$class = new $class();
					$params['markdown'] = $class->doIt($params);
					unset($class);
				} catch (Exception $e) {
					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log($e->getMessage(), 'error');
					}
					/*<!-- endbuild -->*/
				}
			} // foreach ($arrActions as $action)
		} // if (count($arrActions)>0)

		if (boolval(self::getOptions('rewrite_file', 0))) {
			// Rewrite the file as soon as the new content is
			// different than the original one

			if ($markdown!==$params['markdown']) {
				// -----------------------------------------------------
				// In case of a master file with a %INCLUDE chapter1.md%
				// Backup the filename that is being displayed (f.i. master.md)
				$originalFile=$aeSession->get('filename', '');

				// While $params['filename'] is perhaps an included file
				// (like chapter1.md).
				// The markdown.write event should be done on that file, not the
				// 'master' one.
				$aeSession->set('filename', $params['filename']);

				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug->log('Rewrite '.$params['filename'], 'debug');
				}
				/*<!-- endbuild -->*/

				// Rewrite the file on the disk
				$aeEvents = \MarkNotes\Events::getInstance();
				$aeEvents->loadPlugins('task.markdown.write');
				$arr = array('markdown'=>$markdown);
				$args = array(&$arr);
				$aeEvents->trigger('task.markdown.write::run', $args);

				// Restore the filename in the session object
				$aeSession->set('filename', $originalFile);
			} // if ($tmp!==$markdown)
		}

		return true;
	}
}
