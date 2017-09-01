<?php

/**
 * Add a txt export button into the content toolbar
 */

namespace MarkNotes\Plugins\Buttons\Content;

defined('_MARKNOTES') or die('No direct access allowed');

class TXT
{
    private static $layout = 'txt';

    public static function add(&$buttons = null)
    {
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        $title = $aeSettings->getText('export_'.self::$layout, 'Export the note as a TXT document', true);

        $aeSession = \MarkNotes\Session::getInstance();
        $file = $aeSession->get('filename');
        $file = str_replace(DS, '/', $aeFiles->replaceExtension($file, self::$layout));

        // Get the default extension, as specified in the settings.json file
        $default = $aeSettings->getTask()['default'] ?? 'reveal';
        if ($default === self::$layout) {
            // The default extension is txt ==> no need to mention the extension
            $file = $aeFiles->removeExtension($file);
        }

        $url = rtrim($aeFunctions->getCurrentURL(false, false), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';

        $buttons .=
            '<a id="icon_'.self::$layout.'" data-task="file" data-file="'.$url.$file.'" '.
                'href="#" title="'.$title.'">'.
                '<i class="fa fa-file-text-o" aria-hidden="true"></i>'.
            '</a>';

        return true;
    }

	/**
	 * Attach the function and responds to events
	 */
	public function bind()
	{

		$bReturn = false;

		if (stristr(PHP_OS, 'WIN')) {

			$aeSession = \MarkNotes\Session::getInstance();

			// Check if the file, once converted (note.docx) exists
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$filename = $aeSettings->getFolderDocs(true).$aeSession->get('filename');
			$filename  = $aeFiles->replaceExtension($filename, static::$layout);
			$filename = str_replace('/',DS, $filename);

			if (!$aeFiles->fileExists($filename)) {

				// The file didn't exists so, if this plugin is called, we need to
				// be able to run the Pandoc conversion utility, check that the utility is
				// correctly configured

				$aeConvert = \MarkNotes\Tasks\Convert::getInstance($aeSession->get('filename'), static::$layout, 'pandoc');

				if ($aeConvert->isValid()) {

					// Yes, correctly configured, we'll be able to offer the conversion

					$bReturn = true;

				} // if ($aeConvert->isValid())

			} else { // if (!$aeFiles->fileExists($filename))

				$bReturn = true;

			}

		} // if (stristr(PHP_OS, 'WIN'))

		if ($bReturn) {

			$aeEvents = \MarkNotes\Events::getInstance();
			$aeEvents->bind('add.buttons', __CLASS__.'::add');

		}

		return $bReturn;

	}

}
