<?php

/*
 * Export a note to a .odt, thanks to pandoc
 */

namespace MarkNotes\Plugins\Content\ODT;

defined('_MARKNOTES') or die('No direct access allowed');

class Pandoc
{
    private static $layout = 'odt';

	/**
     * Make the conversion
     */
    public static function doIt(&$params = null)
    {

		$bReturn = true;

        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		// ----------------------------------------
		// Call the generic class for file conversion
        $aeConvert = \MarkNotes\Tasks\Convert::getInstance($params['filename'], static::$layout, 'pandoc');

		// Get the filename, once exported (f.i. notes.txt)
		$final = $aeConvert->getFileName();

		// Check if pandoc is installed; if not, check if the exported file already exists
		if (!$aeConvert->isValid()) {

			if (!$aeFiles->fileExists($final)) {

				// No, doesn't exists

		        $bReturn = false;

			}

		} else { // if (!$aeConvert->isValid())

	        $arrPandoc = $aeSettings->getPlugins('options', 'pandoc');

			// Read the content of the .md file
			$filename = $aeSettings->getFolderDocs(true).$params['filename'];

			// Derive filenames

			$slug = $aeConvert->getSlugName();
			$debugFile = $aeConvert->getDebugFileName();

			// Get a copy of the .md note (in /temp folder), run any plugins
			$tempMD = $aeConvert->createTempNote();

			$sScript = $aeConvert->getScript($tempMD, $final);

			$aeConvert->Run($sScript, $final);

		} // if (!$aeConvert->isValid())

		if ($bReturn) $params['output'] = $final;

        return true;

    }

    /**
     * Attach the function and responds to events
     */
    public function bind(string $plugin)
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('export.'.static::$layout, __CLASS__.'::doIt', $plugin);
        return true;
    }
}
