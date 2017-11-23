<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Settings
{
	protected static $hInstance = null;

	private $folderDocs = '';
	private $folderAppRoot = '';
	private $folderWebRoot = '';

	private $json = array();

	private static $bDebugMode = false;

	public function __construct(string $folder = '', array $params = null)
	{
		self::$bDebugMode = false;

		$this->setFolderAppRoot(dirname(dirname(__FILE__)));
		if ($folder !== '') {
			$this->setFolderWebRoot($folder);
		}

		self::readSettings($params);

		return true;
	} // function __construct()

	public static function getInstance(string $folder = '', array $params = null)
	{

		if (self::$hInstance === null) {
			self::$hInstance = new Settings($folder, $params);
		}

		return self::$hInstance;
	}

	/**
	 * $arr is the debug node of the settings.json file, something like :
	 *
	 *  "debug": {
	 *	  "enabled": 1,
	 *	  "development": 1,
	 *	  "logfile": {
	 *		  "template": "%message% %context%"
	 *	  }
	 *  }
	 */
	private function enableDebugMode(array $arr, string $fname) : bool
	{
		/*<!-- build:debug -->*/
		if ($arr!==array()) {
			$aeDebug = \MarkNotes\Debug::getInstance();

			// Debug mode enabled or not
			$debug = boolval($arr['enabled'] ?? 0);

			// Enabled ?
			if ($debug) {
				$aeDebug = \MarkNotes\Debug::getInstance();

				if (isset($arr['development'])) {
					$aeDebug->setDevMode(boolval($arr['development']));
				}

				if (isset($arr['logfile'])) {
					if (isset($arr['logfile']['template'])) {
						$aeDebug->setTemplate($arr['logfile']['template']);
					}
				}

				try {
					if (isset($json['regional']['timezone'])) {
						$aeDebug->setTimezone($json['regional']['timezone']);
					}
				} catch (Exception $e) {
				}

				if (isset($arr['output'])) {
					$aeDebug->setOutput($arr['output']);
				}

				// Once correctly initialized, set the debug mode
				$this->setDebugMode($debug);

				$aeDebug->enable($debug);
				$aeDebug->log('Load settings file : '.$fname, 'debug', 2, false);
			}
		}
		/*<!-- endbuild -->*/

		return true;
	}

	/**
	 * Read settings.json in this order :
	 *   1. the settings.json.dist file to initialize all parameters
	 *   2. If present, the settings.json file i.e. the user settings
	 *	  for the application
	 *   3. If present, the settings.json file that can be found in
	 *	  the note folder
	 *	  (so if the note /docs/marknotes/userguide.md if displayed,
	 *	  check if a file /docs/settings.json and
	 *	  /docs/marknotes/settings.json exists and if so,
	 *	  use it) (check from the parent folder till the deepest one),
	 *   4. Finally, a very specific note.json file : if the note
	 *	  /docs/marknotes/userguide.md if displayed, check if the file
	 *	  /docs/marknotes/userguide.json exists and if so, use it.
	 *
	 * In this order so the file loaded in step 4 will have the priority
	 * and can overwrite global settings
	 */
	private function loadJSON(array $params = null) : array
	{
		$aeJSON = \MarkNotes\JSON::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();

		$json = array();

		// 1. Get the settings.json.dist global file,
		// shipped with each releases of MarkNotes

		// From the application folder (special case when
		// using symbolic paths)
		$noteJSON = $this->getFolderAppRoot().'settings.json.dist';

		if ($aeFiles->fileExists($noteJSON)) {
			$json = $aeJSON->json_decode($noteJSON, true);
		}

		/*<!-- build:debug -->*/
		self::enableDebugMode($json['debug']??array(), $noteJSON);
		/*<!-- endbuild -->*/

		// From the root folder of the web application
		$noteJSON = $this->getFolderWebRoot().'settings.json.dist';
		if ($aeFiles->fileExists($noteJSON)) {
			$arr = $aeJSON->json_decode($noteJSON, true);

			/*<!-- build:debug -->*/
			self::enableDebugMode($arr['debug']??array(), $noteJSON);
			/*<!-- endbuild -->*/

			if (count($arr) > 0) {
				$json = array_replace_recursive($json, $arr);
			}
		}

		// 2. Get the settings.json user's file
		$noteJSON = $this->getFolderWebRoot().'settings.json';

		if ($aeFiles->fileExists($noteJSON)) {
			$arr = $aeJSON->json_decode($noteJSON, true);

			/*<!-- build:debug -->*/
			self::enableDebugMode($arr['debug']??array(), $noteJSON);
			/*<!-- endbuild -->*/

			if (count($arr) > 0) {
				$json = array_replace_recursive($json, $arr);

				// ---------------------------------------------------------
				// @TODO : Remove in future version (@DEPRECTATED)
				// Support old json : before october 2017, old settings.json
				// was like
				//
				// 	{
				//		"debug": 1,
				//		"development": 0,
				//		...
				//  }
				//
				// so debug wasn't an array but just a boolean

				if (!is_array($json['debug'])) {
					$arr=array(
					   'enabled'=>$json['debug']??0,
					   'development'=>$json['development']??0
					);
				} else {
					$arr=$json['debug'];
				}

				//
				// ---------------------------------------------------------
			}
		}

		// 3. The filename shouldn't mention the docs folders, just the filename
		// So, $filename should not be docs/markdown.md but only
		// markdown.md because the folder name will be added later on

		if (isset($params['filename'])) {
			$docRoot = $json['folder'].DS;

			$aeFunctions = \MarkNotes\Functions::getInstance();
			if ($aeFunctions->startsWith($params['filename'], $docRoot)) {
				$params['filename'] = substr($params['filename'], strlen($docRoot));
			}

			// 3. Get the settings.json file that is, perhaps,
			// present in the folder of the note

			// First, be sure that the doc folder has been set

			$this->setFolderDocs($json['folder'] ?? DOC_FOLDER);
			$noteFolder = $this->getFolderDocs(true).str_replace('/', DS, dirname($params['filename']));

			// $noteFolder is perhaps C:\notes\docs\Folder\Sub1\Sub-Sub1\Sub-Sub-Sub1\
			// Process from C:\notes\docs\ till that (so from the top to the deepest)
			// and check if there is a settings.json file

			$folder = $this->getFolderWebRoot();
			$noteFolder = rtrim($noteFolder, DS);

			do {
				// $tree will be equal to docs\Folder\Sub1\Sub-Sub1\Sub-Sub-Sub1\
				$tree = str_replace($folder, '', $noteFolder);

				// Process docs, then Folder, then Sub1, ...
				$subFolder = strrev(basename(strrev($tree)));

				$folder = rtrim($folder, DS).DS.$subFolder;

				$noteJSON = rtrim($folder, DS).DS.'settings.json';

				if ($aeFiles->fileExists($noteJSON)) {
					// Read the settings.json file and merge
					$arr = $aeJSON->json_decode($noteJSON, true);
					$json = array_replace_recursive($json, $arr);

					/*<!-- build:debug -->*/
					self::enableDebugMode($json['debug']??array(), $noteJSON);
					/*<!-- endbuild -->*/
				}
			} while ($folder !== $noteFolder);

			// 4. Get the note_name.json file that is, perhaps,
			// present in the folder of the note.
			// note_name is the note filename with the .json extension of .md

			// if $params['filename'] is equal to /marknotes/userguide.md

			// $dir will be "marknotes/"
			$dir = dirname($params['filename']);
			$dir = ($dir=='.'?'':$dir.DS);

			// $fname will be "userguide.json"
			$aeFiles = \MarkNotes\Files::getInstance();
			$fname=$aeFiles->removeExtension(basename($params['filename'])).'.json';

			// $noteJSON will be c:/sites/notes/docs/marknotes/userguide.json f.i.
			$noteJSON = $this->getFolderDocs(true).$dir.$fname;

			if ($aeFiles->fileExists($noteJSON)) {
				$arr = $aeJSON->json_decode($noteJSON, true);
				$json = array_replace_recursive($json, $arr);

				/*<!-- build:debug -->*/
				self::enableDebugMode($json['debug']??array(), $noteJSON);
				/*<!-- endbuild -->*/
			}
		} // if (isset($params['filename']))

		return $json;
	}

	/**
	* Read the user's settings i.e. the file "settings.json"
	* Initialize class properties
	* @return {bool [description]
	*/
	private function readSettings(array $params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();

		$this->json = $this->loadJSON($params, true);

		// Set regional settings, language and locale
		$arrRegion=$this->json['regional']??array('locale'=>'EN', 'language'=>DEFAULT_LANGUAGE, 'timezone'=>'Europe/London');

		self::setLanguage($arrRegion['language']);
		setlocale(LC_ALL, $arrRegion['locale']);
		date_default_timezone_set($arrRegion['timezone']);

		$this->setFolderDocs($this->json['folder'] ?? DOC_FOLDER);

		return true;
	}

	/**
	* Return the translation of a given text
	 *
	* @param string $variable
	*/
	public function getText(string $variable, string $default = '', bool $jsProtect = false) : string
	{

		static $json_lang=array();

		if ($json_lang === array()) {
			// Load, always, the file in English
			$fname = $this->getFolderAppRoot().'languages/marknotes-'.DEFAULT_LANGUAGE.'.json';
			$json_lang = json_decode(file_get_contents($fname), true);

			// Now, if present, load the second file, the selected language
			// (for instance French).
			$lang = $this->getLanguage();

			if ($lang!==DEFAULT_LANGUAGE) {
				$fname = $this->getFolderAppRoot().'languages/marknotes-'.$lang.'.json';

				if (is_file($fname)) {
					// array_replace_recursive so keys in marknotes-en.json
					// and not in the second file (marknotes-fr.json) are
					// keep and not override. Doing this will allow to be able
					// to show text in english even if not yet translated
					$arr = json_decode(file_get_contents($fname), true);
					$json_lang = array_replace_recursive($json_lang, $arr);
				}
			}
		} // if ($json_lang === array())

		$return = isset($json_lang[$variable]) ? $json_lang[$variable] : trim($default);

		if ($jsProtect) {
			$return = str_replace("'", "\'", html_entity_decode($return));
		}

		// In case of null (i.e. the translation wasn't found, return at least the name of the variable)
		return ($return === null?$variable:$return);
	}

	/**
	 * Small sanitization function to be sure that the user willn't type anything in the settings.json file
	 * for filename properties
	 *
	 * @param  string $fname
	 * @return string
	 */
	private function sanitizeFileName(string $fname) : string
	{
		$fname = trim($fname);

		if ($fname !== '') {
			// should only contains letters, figures or dot/minus/underscore or a slash
			if (!preg_match('/^[A-Za-z0-9-_\.\/]+$/', $fname)) {
				$fname = '';
			}
		} // if ($fname!=='')

		return $fname;
	}

	public function getLanguage() : string
	{
		return $this->language;
	}

	public function setLanguage(string $lang)
	{
		// Can't be longer than 3 characters
		$lang=substr($lang, 0, 3);

		// Verify if the file with translations exists
		$fname = $this->getFolderAppRoot().'languages/marknotes-'.$lang.'.json';

		// If no, use the default language
		$this->language = (is_file($fname) ? $lang : DEFAULT_LANGUAGE);
	}

	public function getDebugMode() : bool
	{
		return self::$bDebugMode ? true : false;
	}

	public function setDebugMode(bool $bOnOff)
	{

		if (self::$bDebugMode) {
			// The debug mode is currently active and
			// a code is asking to disable it
			if (!$bOnOff) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('*** DEBUG MODE IS DISABLING ***', 'debug', 3);
			}
		}

		/*<!-- build:debug -->*/
		if ($bOnOff) {
			self::$bDebugMode = $bOnOff; // Debug mode enabled or not

			error_reporting(E_ALL);

			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->enable($bOnOff);

			$aeJSON = \MarkNotes\JSON::getInstance();
			$aeJSON->debug(true);
		} // if ($bOnOff)
		/*<!-- endbuild -->*/
	}

	public function getHelpersRoot() : string
	{
		return $this->folderAppRoot.'marknotes'.DS.'helpers'.DS;
	}

	/**
	 * The application root folder (due to the use of symbolic links, the .php source files can
	 * be in an another folder than the website itself
	 *
	 * @return string
	 */
	public function getFolderAppRoot() : string
	{
		return $this->folderAppRoot;
	}

	public function setFolderAppRoot($folder)
	{
		// Respect OS directory separator
		$folder = str_replace('/', DS, $folder);

		// Be sure that there is a slash at the end
		$folder = rtrim($folder, DS).DS;

		$this->folderAppRoot = $folder;
	}

	/**
	 * Return the name of the folder (relative) of the documents folder
	 *
	 * @param  bool  True  : return the full path (f.i. 'C:\Repository\notes\docs\')
	 *			   False : return the relative one (f.i. 'docs')
	 * @return string
	 */
	public function getFolderDocs(bool $absolute = true) : string
	{
		return ($absolute?$this->getFolderWebRoot():'').$this->folderDocs;
	} // function getFolderDocs

	public function setFolderDocs($folder)
	{

		// Respect OS directory separator
		$folder = str_replace('/', DS, $folder);

		// Be sure that there is a slash at the end
		$folder = rtrim($folder, DS).DS;

		$this->folderDocs = $folder;
	} // function setFolderDocs

	/**
	 * Return the path to the tmp folder at the webroot.
	 * If the folder doesn't exist yet, create it
	 *
	 * @return string
	 */
	public function getFolderTmp() : string
	{
		$folder = rtrim($this->folderWebRoot, DS).DS.'tmp';

		if (!is_dir($folder)) {
			mkdir($folder, CHMOD_FOLDER);
		}

		if (is_dir($folder)) {
			if (!file_exists($fname = $folder.'/.gitignore')) {
				file_put_contents($fname, '# Ignore everything'.PHP_EOL.'*');
			}

			if (!file_exists($fname = $folder.'/.htaccess')) {
				file_put_contents($fname, 'deny from all');
			}
		}

		return $folder.DS;
	}

	/**
	 * Return the path to the tmp folder at the webroot.
	 * If the folder doesn't exist yet, create it
	 *
	 * @return string
	 */
	public function getFolderCache() : string
	{
		$folder = rtrim($this->folderWebRoot, DS).DS.'cache';

		if (!is_dir($folder)) {
			mkdir($folder, CHMOD_FOLDER);
		}

		if (is_dir($folder)) {
			if (!file_exists($fname = $folder.'/.gitignore')) {
				file_put_contents($fname, '# Ignore everything'.PHP_EOL.'*');
			}

			if (!file_exists($fname = $folder.'/.htaccess')) {
				file_put_contents($fname, 'deny from all');
			}
		}

		return $folder.DS;
	}
	/**
	 * Return the root folder of the website (f.i. 'C:\Repository\notes\')
	 *
	 * @return string
	 */
	public function getFolderWebRoot() : string
	{
		return $this->folderWebRoot;
	}

	public function setFolderWebRoot(string $folder)
	{
		$this->folderWebRoot = rtrim($folder, DS).DS;
	}

	public function getFolderLibs() : string
	{
		return $this->getFolderAppRoot().'libs'.DS;
	}

	public function getFolderTasks() : string
	{
		return $this->getFolderAppRoot().'classes'.DS.'tasks'.DS;
	}

	public function getFolderTemplates() : string
	{
		return $this->getFolderAppRoot().'templates'.DS;
	}

	/**
	 * Return the template to use for the screen display or
	 * the html output.
	 * Get the template info from the settings.json when the
	 * node 'templates' is found there
	 *
	 * @param  string $default Default filename with extension
	 *					(f.i. 'screen' or 'html')
	 * @return string	Full path to the file
	 */
	public function getTemplateFile(string $default = 'screen') : string
	{

		$aeFiles = \MarkNotes\Files::getInstance();

		$tmpl = $default;
		if (isset($this->json['templates'])) {
			if (isset($this->json['templates'][$default])) {
				$tmpl = $this->sanitizeFileName($this->json['templates'][$default]);
			}
		}

		if ($tmpl !== '') {
			// Get the filename (f.i. "screen" (or "screen.php")
			$fname = $this->getFolderTemplates().$tmpl;

			// The file isn't found; perhaps the extension wasn't mentionned
			// If no extension mentionned; default is .php
			if (!$aeFiles->fileExists($fname)) {
				if ($aeFiles->fileExists($fname.'.php')) {
					$fname.='.php';
				}
			}

			if (!$aeFiles->fileExists($fname)) {
				// The specified template doesn't exists.
				// Back to the default one;
				/*<!-- build:debug -->*/
				if ($this->getDebugMode()) {
				   $aeDebug = \MarkNotes\Debug::getInstance();
				   $aeDebug->log("Template [".$fname."] not found, ".
				   	"please review your settings.json file","warning");
				}
				/*<!-- endbuild -->*/

				$fname = '';
			}
		} else { // if ($tmpl!=='')
			if ($aeFiles->fileExists($this->getFolderTemplates().$tmpl.'.php')) {
				$fname = $this->getFolderTemplates().$tmpl.'.php';
			} else {
				// No template at all

				$fname='';
			}
		} // if ($tmpl!=='')

		$fname = str_replace('/', DS, $fname);

		/*<!-- build:debug -->*/
		if (($fname!=='') && (self::getDebugMode())) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('Template for '.$default.' is '.$fname, 'debug');
		}
		/*<!-- endbuild -->*/

		return $fname;
	}

	/**
	 * Return the max width size for images (from settings.json)
	 *
	 * @return string
	 */
	public function getPageImgMaxWidth() : string
	{
		return $this->json['page']['img_maxwidth'] ?? IMG_MAX_WIDTH;
	} // function getPageImgMaxWidth()

	/**
	 * Return the name of the website
	 *
	 * @return string
	 */
	public function getSiteName() : string
	{
		return trim($this->json['site_name'] ?? '');
	}

	/**
	 * Retrieve if a specific tool like for instance 'decktape' is configured in the settings.json file
	 *
	 * The json "convert" entry looks like this :
	 *	 "convert": {
	 *		 "pandoc": {
	 *			 "script" : "c:\\christophe\\tools\\pandoc\\pandoc.exe",
	 *			 "options" : "--latex-engine=xelatex -V geometry:margin=1in -o"
	 *		 }
	 *
	 * This function will return an array with every entries below the name of the converting tool but
	 * only if the tool is found i.e. if the "script" file exists on the disk
	 *
	 */
	public function getConvert(string $sTool) : array
	{
		/*<!-- build:debug -->*/
		$aeDebug = \MarkNotes\Debug::getInstance();
		$aeDebug->here("*** OBSOLETE - The convert node doesn't exists anymore. See plugins->options->export", 10);
		/*<!-- build:debug -->*/
		die("Died in ".__FILE__.", line ".__LINE__);
		/*<!-- endbuild -->*/
		/*<!-- endbuild -->*/
		$aeFiles = \MarkNotes\Files::getInstance();

		$arr = array();

		if (isset($this->json['convert'])) {
			if (isset($this->json['convert'][$sTool])) {
				if ($aeFiles->fileExists($this->json['convert'][$sTool]['script'])) {
					$arr = $this->json['convert'][$sTool];
				}
			}
		}

		return $arr;
	}

	/**
	 * Return a node from the "Page" JSON entry
	 */
	public function getPage(string $node = '', $default = '')
	{
		return $this->json['page'][$node] ?? $default;
	} // function getPage()

	/**
	 * Get locale
	 *
	 * @return bool
	 */
	public function getLocale() : string
	{
		// Set regional settings, language and locale
		$arrRegion=$this->json['regional']??array('locale'=>'EN', 'language'=>DEFAULT_LANGUAGE, 'timezone'=>'Europe/London');

		// Be sure to have en-US (minus) and not en_US (underscore)
		return str_replace('_', '-', trim($arrRegion['locale']));
	}

	/**
	 * Get timezone
	 *
	 * @return bool
	 */
	public function getTimezone() : string
	{
		return $this->json['regional']['timezone'] ?? 'Europe/London';
	}

	/**
	 * Return an information of the plugins node
	 *
	 * $info can be, f.i,
	 *
	 *	plugins.content.html = will return the list of all plugins
	 *		  under content->html
	 *	plugins.options.bootstrap.bullet = will return the value of the
	 *		  bullet attribute
	 *	/regex : by specifying the /; this indicate to not search inside
	 *		  the "plugins" node which is the default
	 *
	 */
	public function getPlugins(string $info = '', array $default = array()) : array
	{
		$arr = $default;

		// In case of, remove the ending dot.
		$info=rtrim($info, '.');

		// If $info is like "plugins.options.export.copy",
		// the "plugins." prefix isn't needed, remove it

		if (substr($info, 0, 1)!=='/') {
			if (substr($info, 0, 8)=='plugins.') {
				// Keep only "options.export.copy"
				$info=substr($info, 8);
			}
			if (isset($this->json['plugins'])) {
				$arr = $this->json['plugins'];
			}
		} else {
			// The call was for a root node (f.i. /regex) and not,
			// in the plugins" node.
			$info=substr($info, 1);
			$arr = $this->json;
		}

		if ($info !== '') {
			// Convert, f.i., 'options.bootstrap.bullet' into an array
			// of three positions
			$tmp = explode('.', $info);

			// Process every positions so, for 'options.bootstrap.bullet',
			// get plugins, then options, then bootstrap and finally bullet
			for ($i=0; $i<count($tmp); $i++) {
				$node=$tmp[$i];
				if (isset($arr[$node])) {
					$arr = $arr[$tmp[$i]];
				} else {
					/*<!-- build:debug -->*/
					if (self::getDebugMode()) {
						$aeDebug = \MarkNotes\Debug::getInstance();
						$aeDebug->log("Called for [".$info."] but [".$node."] is not found in settings.json. In that case, consider therefore the task enabled by default and the file plugins".DS.str_replace('.', DS, $info)." will be loaded", "debug");
					}
					/*<!-- endbuild -->*/
					$arr = $default;
				}
			}
		}

		return $arr;
	}

	/**
	 * Open the package.json file and retrieve an
	 * information like the version number from there
	 *
	 * @return string
	 */
	public function getPackageInfo(string $info = 'version') : string
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeJSON = \MarkNotes\JSON::getInstance();
		$sReturn = '';

		$fname = $this->getFolderAppRoot().'package.json';
		if ($aeFiles->fileExists($fname)) {
			$json = $aeJSON->json_decode($fname, true);
			$sReturn = $json[$info];
		}
		return $sReturn;
	}
}
