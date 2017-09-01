<?php

/**
 * Generic class used by different converter like /plugins/content/docx.php,
 * /plugins/content/pdf/pandoc.php, ...
 */

namespace MarkNotes\Tasks;

use \Symfony\Component\Yaml\Yaml;

defined('_MARKNOTES') or die('No direct access allowed');

class Convert
{
    protected static $hInstance = null;

	private $_sourceFileName = ''; // File to convert
	private $_layout = '';         // For instance docx
	private $_method = '';         // For instance "pandoc"
	private $_options = null;      // plugins options (f.i. the plugins->options->pandoc entry)

    public function __construct(string $filename='', string $layout='', string $method='')
    {
		$this->_sourceFileName=$filename;
		$this->_layout=$layout;
		$this->_method=$method;

		$this->_options=self::getOptions();

        return true;
    }

    public static function getInstance(string $filename, string $layout, string $method='')
    {
        if (self::$hInstance === null) {
            self::$hInstance = new Convert($filename, $layout, $method);
        }

        return self::$hInstance;
    }

	/**
	 * Return the options from settings.json, f.i. then plugins->options->pandoc entry
	 */
	public function getOptions() : array {

		if ($this->_options==null) {
		   $aeSettings = \MarkNotes\Settings::getInstance();
		   $this->_options=$aeSettings->getPlugins('options', $this->_method);
	    }

	   return $this->_options;

	}

	public function isValid() : bool {

		$bReturn=true;

		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('Plugin options : '.json_encode($this->_options), 'debug');
		}
		/*<!-- endbuild -->*/

		if ($this->_options === array()) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log('Error, options should be specified', 'error');
			}
			/*<!-- endbuild -->*/

			$bReturn=false;

		}

		if (($bReturn) && ($this->_method==='pandoc')) {

			// Be sure that the script pandoc.exe is well installed on the system

	        $aeFiles = \MarkNotes\Files::getInstance();
			// $sScriptName string Absolute filename to the pandoc.exe script
			if (!$aeFiles->fileExists($sScriptName = $this->_options['script'])) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug->log('File '.$sScriptName.' didn\'t exists', 'error');
				}
				/*<!-- endbuild -->*/

				$bReturn=false;

			}

		}

		return $bReturn;
	}

    /**
     * Taking the name of the note, provide the name of the file that should be created
     * F.i. for file c:\sites\marknotes\docs\so_nice_app.md return
	 * c:\sites\marknotes\docs\so_nice_app.pdf when the layout is .pdf
     */
    public function getFileName() : string
    {

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		$fname=$this->_sourceFileName;

        // $fname should be an absolute filename; not a relative one
        if (strpos($fname, $aeSettings->getFolderDocs(true)) === false) {
            $fname = $aeSettings->getFolderDocs(true).ltrim($fname, DS);
        }

        $fname = $aeFiles->replaceExtension(str_replace('/', DS, $fname),$this->_layout);

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('Target file : '.$fname, 'info');
		}
		/*<!-- endbuild -->*/

        return $fname;
    }

	/**
	 * Return a "slug" from a filename (f.i. return "connectas" when the filename is
	 * "connect-as.md")
	 */
	public function getSlugName() : string
	{
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();

		$slug = $aeFiles->removeExtension(basename($this->_sourceFileName));
		$slug = $aeFunctions->slugify($slug);

		return $slug;

	}

	/**
	 * Return a "debug filename" (f.i. connect-as_debug.log)
	 */
	public function getDebugFileName() : string
	{
		return self::getSlugName().'_debug.log';
	}

	/*
	 * Read the note and call any plugins.
	 * Generate a temporary version of the note in the temporary folder
	 */
	public function createTempNote() : string {

        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeMarkdown = \MarkNotes\FileType\Markdown::getInstance();

		$fname=$this->_sourceFileName;
		if(!is_file($fname)) $fname=$aeSettings->getFolderDocs(true).$fname;

		// The read method is also responsible to run any markdown.read plugins
		$content=$aeMarkdown->read($fname);

		// Derive the temporary filename
		$filename=$aeSettings->getFolderTmp().self::getSlugname($fname).'.md';

		// Check if there is a YAML header and if so, add in back in the .md file
        $yaml=trim($aeSession->get('yaml',''));

        if ($yaml!=='') {

			$lib=$aeSettings->getFolderLibs()."symfony/yaml/Yaml.php";

			if (is_file($lib)) {

				include_once $lib;

				// Yaml::dump will add double-quotes so remove them
				$content=
					"---".PHP_EOL.
					str_replace('\\n',PHP_EOL,trim(Yaml::dump($yaml),'"')).PHP_EOL.
					"---".PHP_EOL.PHP_EOL.
					$content;
			}
        }

		// Return the temporary filename or an empty string
		return $aeFiles->createFile($filename,$content) ? $filename : '';

	}

	private function getPandocScript(string $InputFileName, string $TargetFileName) : string
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$debugFile=self::getDebugFileName();
		$slug=self::getSlugName();

		// Get the template to use, if any
		$template = '';
		if ($this->_layout==='docx') {
           $template = $aeSettings->getTemplateFile($this->_layout);
		   if ($template!=='') $template='--reference-docx="'.$template.'" ';
	    }

		// Retrieve the options for this conversion
		// Found in settings.json->plugins->options->METHOD->options
		// Method is a supported method like "pandoc"
		$options = isset($this->_options[$this->_layout]) ? $this->_options[$this->_layout] : '';

		// Executable (pandoc.exe)
		$script = '"'.($this->_options['script']??'').'" ';

		// Output filename
		$outFile='-o "'.basename($TargetFileName).'" ';
		$inFile='"'.basename($InputFileName).'"' ;

		$killFiles='';

		if (!$aeSettings->getDevMode()) {
			// Once copied, kill from temp
			$killFiles=
	            'if exist "'.$TargetFileName.'" ('.PHP_EOL.
				'   del "'.basename($TargetFileName).'"'.PHP_EOL.
				'   del "'.$debugFile.'"'.PHP_EOL.
	            '   del "'.$inFile.'"'.PHP_EOL.
				')';
		} // if (!$aeSettings->getDevMode())

		$sScript =
            '@ECHO OFF'.PHP_EOL.
			// Change default code page of Windows console to UTF-8
	        // @link : https://superuser.com/questions/269818
            'chcp 65001'.PHP_EOL.
			// Make the temporary folder the working folder
			'cd "'.$aeSettings->getFolderTmp().'"'.PHP_EOL.
			// Kill the old debug informations
            'if exist "'.$debugFile.'" del "'.$debugFile.'"'.PHP_EOL.
			// run the tool
            $script.$template.$options.$outFile.$inFile.'> '.$debugFile.' 2>&1'.PHP_EOL.
			// Copy the result file in the correct folder
            'copy "'.basename($TargetFileName).'" "'.$TargetFileName.'"'.PHP_EOL.
			$killFiles;

		return $sScript;

	}

	public function getScript(string $InputFileName, string $TargetFileName) : string
	{

		$sScript = '';

		if ($this->_method==='pandoc') {
			$sScript = self::getPandocScript($InputFileName, $TargetFileName);
		}

		return $sScript;

	}

    public function run(string $sScript, string $TargetFileName)
    {

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

        $fScriptFile = $aeSettings->getFolderTmp().self::getSlugName().'.bat';

		if (!$aeFiles->createFile($fScriptFile,$sScript)) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log('Error while creating file '.$fScriptFile,'error');
			}
			/*<!-- endbuild -->*/

		}

	    // Run the script.
		// This part can be long depending on the size of the .md file
	    $output = array();
	    exec("start cmd /c ".$fScriptFile, $output);

		// Once the exec() statement is finished

		if (!$aeFiles->fileExists($TargetFileName)) {

			$msg = $aeSettings->getText('file_not_found', 'The file [%s] doesn\\&#39;t exists');
			$msg = str_replace('%s', '<strong>'.$final.'</strong>', $msg);

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->here('#DebugMode# - File '.$TargetFileName.' not found', 10);
			}
			/*<!-- endbuild -->*/

			echo $msg.PHP_EOL.PHP_EOL;

			echo '<p>Check to start <strong>'.$fScriptFile.'</strong> manually; indeed, sometimes it doesn\'t work within PHP but well manually; with the user\'s OS credentials (PHP permissions problems). Then, just refresh this page.</p>';

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {

				$debugFile=self::getDebugFileName();

				if ($aeFiles->fileExists($debugFile)) {
					$content = file_get_contents ($debugFile);
					echo '<h3>Content of the debug file : '.$debugFile.'</h3>';
					echo "<pre style='background-color:yellow;'>".$content."</pre>";
				}
			}
			/*<!-- endbuild -->*/

		} else { // if (!$aeFiles->fileExists($final))

			// The file has been correctly exported, the batch is no more needed

			if (!$aeSettings->getDevMode()) {
				// Kill the script file only when not Developper mode
				unlink ($fScriptFile);
			}

		} // if (!$aeFiles->fileExists($final))

/*
		die(__FILE__." - ".__LINE__. " -  called, is this still needed ?");

        // If the filename doesn't mention the file's extension, add it.
        if (substr($params['filename'], -3) != '.md') {
            $params['filename'] .= '.md';
        }

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $layout = isset($params['layout']) ? $params['layout'] : '';

        // Retrieve the fullname of the file that will be generated
        // The task can be "docx" or "pdf" i.e. the file's extension
        $final = self::getFileName($params['filename'], $params['task']);

        // And check if the file already exists => faster than creating on-the-fly
        if ($aeFiles->fileExists($final)) {
            $fMD = $aeSettings->getFolderDocs(true).$aeFiles->replaceExtension($params['filename'], 'md');
            if (filemtime($final) < filemtime($fMD)) {
                // The note has been modified after the generation of the .pdf => no more up-to-date
                $final = '';
            }
        }

        // Doesn't exists yet ? Create it
        if (($final === '') || (!$aeFiles->fileExists($final))) {

            // Try to use the best Converter
            $converter = '';

            // The exec() function should be enabled to use deckTape
            $aeFunctions = \MarkNotes\Functions::getInstance();
            if (!$aeFunctions->ifDisabled('exec')) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    if (in_array($layout, array('reveal', 'remark'))) {

                        // deckTape is only for slideshow view and not for HTML view
                        $converter = ($aeSettings->getConvert('decktape') !== array() ? 'decktape' : '');
                    } else { // if (in_array($layout, array('reveal', 'remark')))

                        // Check for pandoc
                        $converter = ($aeSettings->getConvert('pandoc') !== array() ? 'pandoc' : '');
                    }
                } // if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            } // if (!$aeFunctions->ifDisabled('exec'))

            switch ($converter) {
                case 'decktape':
                    $aeConvert = \MarkNotes\Tasks\Converter\Decktape::getInstance();
                    break;

                case 'pandoc':
                    $aeConvert = \MarkNotes\Tasks\Converter\Pandoc::getInstance();
                    break;

                default:
                    $aeConvert = \MarkNotes\Tasks\Converter\Dompdf::getInstance();
                    break;
            }

            $final = $aeConvert->run($params);
        }

        // Return the fullname of the file
        return $final;*/
    }
}
