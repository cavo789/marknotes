<?php

namespace AeSecureMDTasks;

class Clean
{
    /**
     * Each time a note is displayed (let's say note.md), the HTML version is saved (note.html)
     * Also, when the note is displayed in a slideshow mode, a file note_slideshow.html is created
     *
     * After weeks, the folder with the notes will contain a lot of .html files.
     *
     * These files can be safely removed since, when the file doesn't exists, the file will then be created
     * during the first display of the note.
     *
     * This function will therefore kill these unneeded files
     *
     * @return
     */
    public static function Run()
    {

        $aeDebug=\AeSecure\Debug::getInstance();
        $aeSettings=\AeSecure\Settings::getInstance();

        $arrFiles=\AeSecure\Functions::array_iunique(\AeSecure\Files::rglob('*.html', $aeSettings->getFolderDocs(true)));

        $arrDebug=array();

        if (count($arrFiles)>0) {
            foreach ($arrFiles as $file) {
                /*<!-- build:debug -->*/
                if ($aeSettings->getDebugMode()) {
                    $arrDebug['debug'][]=$aeDebug->log('Kill '.utf8_encode($file), true);
                }
                /*<!-- endbuild -->*/
                if (is_writable($file)) {
                    unlink($file);
                }
            }
        } else { // if(count($arrFiles)>0)

            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                $arrDebug['debug'][]='There are no .html files in '.$aeSettings->getFolderDocs(false);
            }
            /*<!-- endbuild -->*/
        } // if(count($arrFiles)>0)

        echo \AeSecure\JSON::json_return_info(
            array(
            'status'=>1,
            'msg'=>sprintf(
                $aeSettings->getText('settings_clean_done', '%d files have been removed in the folder [%s]'),
                count($arrFiles),
                $aeSettings->getFolderDocs(false)
            )),
            $arrDebug
        );

        die();
    } // function Run()
} // class Clean
