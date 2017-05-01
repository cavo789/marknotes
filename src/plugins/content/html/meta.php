<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Meta
{
    public static function doIt(&$html = null)
    {
        if (trim($html) === '') {
            return true;
        }

        $arr = array('<!--%META_DATA%-->','<!--%FAVICON%-->');
        $bFound = false;
        foreach ($arr as $key) {
            if (strpos($html, $key) !== false) {
                $bFound = true;
                break;
            }
        }

        if ($bFound === false) {
            return false;
        }

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        foreach ($arr as $key) {
            switch ($key) {
                case '<!--%META_DATA%-->':
                    $filename = 'meta.txt';
                    break;
                case '<!--%FAVICON%-->':
                    $filename = 'favicon.txt';
                    break;
            }

            if (!$aeFiles->fileExists($filename)) {
                $filename = __DIR__.'/meta/'.$filename.'.dist';
            }

            if ($aeFiles->fileExists($filename)) {

                // Read the meta file and inject its content in the HTML
                $content = file_get_contents($filename);
                $html = str_replace($key, $content, $html);

                // Replace some variables
                $aeFunctions = \MarkNotes\Functions::getInstance();

                $html = str_replace('%ROOT%', rtrim($aeFunctions->getCurrentURL(false, false), '/'), $html);
            }
        }


        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('display.html', __CLASS__.'::doIt');
        return true;
    }
}
