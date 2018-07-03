<?php
/**
 * Generate the form for the editor; based on SimpleMDE
 * @link https://github.com/sparksuite/simplemde-markdown-editor
 */
namespace MarkNotes\Plugins\Task\Export;

defined('_MARKNOTES') or die('No direct access allowed');

use Symfony\Component\Yaml\Exception\ParseException;

class Form extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.editor';
	protected static $json_options = 'plugins.options.page.html.editor';

	/**
	* Determine if this plugin is needed or not
	*/
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			$aeSession = \MarkNotes\Session::getInstance();
			$bCanRun = boolval($aeSession->get('authenticated', 0));
		}

		if (!$bCanRun) {
			$aeSettings = \MarkNotes\Settings::getInstance();

			$return = array();
			$return['status'] = 0;
			$return['message'] = $aeSettings->getText('not_authenticated', 'You need first to authenticate', true);

			header('Content-Type: application/json');
			echo json_encode($return, JSON_PRETTY_PRINT);
		}

		return $bCanRun;
	}

	/**
	 * Load all .js files needed by the editor
	 * @return string [description]
	 */
	private static function getJS() : string {

		$aeFunctions = \MarkNotes\Functions::getInstance();

		$rootURL = rtrim($aeFunctions->getCurrentURL(), '/');
		$url = $rootURL.'/marknotes/plugins/page/html/editor/';

		// Add keymaster.js
		$script =
			"\n<script ". "src=\"".$url."libs/keymaster/keymaster.js\" defer=\"defer\"></script>\n";
		$script .=
			"\n<script ". "src=\"".$url."keys.js\" defer=\"defer\"></script>\n";

		// Get the options for the plugin
		$bSpellCheck = boolval(self::getOptions('spellchecker', true));

		// Get the options for the plugin
		$default = "'en':'English','es':'Spanish','fr':'French','it':'Italian','nl':'Dutch'";

		$sLang = trim(self::getOptions('language_to', $default));
		if ($sLang==='') {
			$sLang = $default;
		}

		// Should be a double quote and not a single
		$sLang = str_replace("'", '"', $sLang);

		// Get options
		$aeSettings = \MarkNotes\Settings::getInstance();
		$arr = $aeSettings->getPlugins('plugins.options.task.upload');

		// Get the list of allowed mime types
		// This types will allow the Upload batch form (Dropzone)
		// to not upload unallowed files
		$arrMime = $arr['accept_upload_mime']??array();
		$mime = '';
		if (count($arrMime)>0) {
			foreach ($arrMime as $tmp) {
				if (strpos($tmp, '/')===false) {
					$tmp.='/*';
				}
				$mime .= $tmp.',';
			}
			$mime = rtrim($mime, ',');
		}

		// Maximum filesize allowed
		$max_size = $arr['max_size']??5;

		// Output the configuration
		$script .= "<script>\n".
			"marknotes.editor={};\n".
			"marknotes.editor.upload = {};\n".
			"marknotes.editor.upload.accepted_mime = \"".$mime."\";\n".
			"marknotes.editor.upload.max_size = ".$max_size.";\n".
			"marknotes.editor.language_to={".$sLang."};\n".
			"marknotes.editor.spellChecker=".($bSpellCheck?"true":"false").";\n".
			"</script>";

		$js = $aeFunctions->addJavascriptInline($script);

		return $js;
	}

	/**
	 *  Load all .css files needed by the editor
	 * @return string [description]
	 */
	private static function getCSS() : string {

		$aeFunctions = \MarkNotes\Functions::getInstance();

		$rootURL = rtrim($aeFunctions->getCurrentURL(), '/');
		$url = $rootURL.'/marknotes/plugins/page/html/editor/';

		$prefix = "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ";

		// Load tui.editor dependencies
		// https://github.com/nhnent/tui.editor/blob/master/docs/getting-started-with-bower.md
		$css =
			$prefix."href=\"".$url."libs/tui-editor/codemirror.css\">".
			$prefix."href=\"".$url."libs/tui-editor/github.css\">".
			$prefix."href=\"".$url."libs/tui-editor/tui-editor.min.css\">".
			$prefix."href=\"".$url."libs/tui-editor/tui-editor-contents.min.css\">";

		// Dropzone
		$css .=
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$rootURL."/marknotes/plugins/page/html/".
			"upload/libs/dropzone/dropzone.min.css\">\n";

		// The overwrite of the CSS
		$css .=
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$url."editor.css\">\n";

		return $css;

	}

	/**
	 * Get the HTML of the editor
	 * @return string [description]
	 */
	private static function getHTML(string $fullname) : string {
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the template
		$folder = dirname(dirname(__DIR__)).DS.'page/html/editor/assets/';
		$form = $folder.'editor.frm';

		if ($aeFiles->exists($form)) {

			$html = $aeFiles->getContent($form);

			// Fullname of the note
			$html = str_replace('%FULLNAME%', utf8_encode($fullname), $html);

			// --------------------------------------------------------
			// Define where to store uploaded files (images or not)
			$docs = $aeSettings->getFolderDocs(true);
			$uploadFolder = dirname($fullname);
			$uploadFolder = rtrim(str_replace($docs, '', $uploadFolder),DS).DS;

			// The subfolder is the name the edited file without
			// the extension
			$uploadSubFolder = basename($aeFiles->RemoveExtension($fullname));

			$html = str_replace('%UPLOADFOLDER%', base64_encode($uploadFolder), $html);
			$html = str_replace('%SUBFOLDER%', base64_encode($uploadSubFolder), $html);

			// --------------------------------------------------------
			// Get the options for the plugin
			// Check if the spellcheck should be enabled or not
			$bSpellCheck = boolval(self::getOptions('spellchecker', true));
			$spellcheck = ($bSpellCheck ? 'spellcheck="true"' : '');
			$html = str_replace('%SPELLCHECK%', $spellcheck, $html);

			// --------------------------------------------------------
			// Set the language so the browser can know in which language
			// the content will be, probably, written
			$lang=$aeSettings->getLanguage();
			$html = str_replace('%LANGUAGE%', $lang, $html);

			// --------------------------------------------------------
			// Read the markdown of the note
			// In the edit form; keep encrypted data ...
			// unencrypted (we need to be able to see and update them)
			$params['encryption'] = 0;
			$aeMD = \MarkNotes\FileType\Markdown::getInstance();
			$markdown = $aeMD->read($fullname, $params);

			// tui.editor don't like to edit HTML tags
			// like <div>, <span>, <encrypt>, ... and remove them
			// even when the "useDefaultHTMLSanitizer" is set
			// to false in the editor constructor.
			//
			// To avoid to lose tags, escape < and > here
			// The plugins/page/html/editor/editor.js file will µ
			// unescape back when the load is done (function
			// fnPluginEditLoaded)
			$markdown = str_replace('<', '&lt;', $markdown);
			$markdown = str_replace('>', '&gt;', $markdown);

			$html = str_replace('%SOURCE%', $markdown, $html);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				// Add the filename for debugging purposes
				$html = '<!-- '.$form.'-->'.PHP_EOL.$html;
			}
			/*<!-- endbuild -->*/

		} else {
			/*<!-- build:debug -->*/
			header('Content-Transfer-Encoding: ascii');
			header('Content-Type: text/html; charset=utf-8');
			$aeFunctions->fileNotFound($form);
			/*<!-- endbuild -->*/
		}

		return $html;
	}

	/**
	 *
	 * Call plugins that are responsible to add icons to the toolbar
	 * and generate the JS to add buttons to the interface
	 *
	 * @return [type] [description]
	 */
	private static function getEditorButtons() {

		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('editor');
		$buttons = array();
		$args = array(&$buttons);
		$aeEvents->trigger('editor::add.buttons', $args);

		$arrButtons = $args[0];

		$js = '';
		$css = '';
		$script = '';

		if (count($arrButtons)>0) {

			$tmp = '';
			$debug = '';

			// --------------------------------------------
			// 1. Get external .js scripts
			// See plugins/editor/zip.php for an example.
			$aeFunctions = \MarkNotes\Functions::getInstance();

			$rootURL = rtrim($aeFunctions->getCurrentURL(), '/');
			$url = $rootURL.'/marknotes/plugins/editor/';

			if (isset($arrButtons['script'])) {
				foreach ($arrButtons['script'] as $key=>$value) {
					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$debug =
							"<!-- Loaded by ".$key." --> \n";
					}
					/*<!-- endbuild -->*/
					$script .= $debug."<script src=\"".$url.$value."\"></script>\n";
				}
			}

			// --------------------------------------------
			// 2. Get inline js
			if (isset($arrButtons['js'])) {
				foreach ($arrButtons['js'] as $key=>$value) {

					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$debug =
							"	// ".str_repeat('-', 60)."\n".
							"	// Code from ".$key.".js\n".
							"	// ".str_repeat('-', 60)."\n";
					}
					/*<!-- endbuild -->*/

					$tmp .= $debug.'	'.trim($value).PHP_EOL;
				}
			}

			// fnPluginEditAddButtonsToolbar() is called by
			// marknotes\plugins\page\html\editor\editor.js
			$js = "<script>\n".
				"function fnPluginEditAddButtonsToolbar(editor, filename) {\n".
				"	// editor is a pointer to the editor object and \n".
				"	// filename is the base64 encoding of the file being edited\n".
				"	var toolbar = editor.getUI().getToolbar();\n".
					$tmp.
				"}\n".
				"</script>\n";

			// --------------------------------------------
			// 3. Process the css
			// Get inline CSS
			$css = '';

			$tmp = '';
			if (isset($arrButtons['css'])) {
				foreach ($arrButtons['css'] as $key=>$value) {

					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$debug =
							"	/*\n".
							"		Code from ".$key.".css\n".
							"	*/\n";
					}
					/*<!-- endbuild -->*/

					$tmp .= $debug.'	'.trim($value).PHP_EOL;
				}
			}
			$css .= "<style>\n".$tmp."</style>\n";
		}

		return $script."\n".$js."\n".$css;

	}

	/**
	* Return the code for showing the login form and respond to the login action
	*/
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		header('Content-Type: text/plain; charset=utf-8');
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// Get the filename from the querystring
		$filename = $aeFunctions->getParam('param', 'string', '', true);

		if ($filename!=='') {
			$filename = json_decode(urldecode($filename));

			// Be sure to have the .md extension
			$filename = $aeFiles->RemoveExtension($filename).'.md';

			// Make filename absolute
			$fullname = $aeFiles->makeFileNameAbsolute($filename);

			if (!$aeFiles->exists($fullname)) {
				echo str_replace('%s', '<strong>'.$filename.'</strong>', $aeSettings->getText('file_not_found'));
				die();
			}
		} else {
			echo $aeSettings->getText('error_filename_missing');
			die();
		}

		// Get the default language
		$lang=$aeSettings->getLanguage();

		// and now, try to retrieve the language used in the note;
		// this from the YAML block if present
		$yaml = $aeSession->get('yaml', array());

		if ($yaml !== array()) {
			$lib=$aeSettings->getFolderLibs()."symfony/yaml/Yaml.php";
			if ($aeFiles->exists($lib)) {
				include_once $lib;
				try {
					$arrYAML = \Symfony\Component\Yaml\Yaml::parse($yaml);
					$lang = $arrYAML['language']??$lang;
				} catch (ParseException $exception) {
					/*<!-- build:debug -->*/
					if ($aeSettings->getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						if ($aeDebug->getDevMode()) {
							printf('Task edit.form - Unable to parse the YAML string: %s', $exception->getMessage());
						}
					}
					/*<!-- endbuild -->*/
				}
			}
		}

		// Call each function and return the HTML of the form, JS and CSS
		// needed by the editor and, the list of buttons (JS and CSS)
		echo
			self::getHTML($fullname).PHP_EOL.
			self::getJS().PHP_EOL.
			self::getCSS().PHP_EOL.
			self::getEditorButtons();

		return true;
	}
}
