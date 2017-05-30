<?php

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Write
{

    /**
     * The $markdown file will be written on disk
     */
    public static function writeMD(&$markdown = null)
    {
        if (trim($markdown) === '') {
            return true;
        }

        // Be sure to have content with LF and not CRLF in order to be able to use
        // generic regex expression (match \n for new lines)
        $markdown = str_replace("\r\n", "\n", $markdown);

        // -----------------------------------------------------------------------
        // URL Cleaner : Make a few cleaning like replacing space char in URL or in image source
        // Replace " " by "%20"

        $matches = array();
        if (preg_match_all('/<img *src *= *[\'|"]([^\'|"]*)/', $markdown, $matches)) {
            foreach ($matches[1] as $match) {
                $sMatch = str_replace(' ', '%20', $match);
                $markdown = str_replace($match, $sMatch, $markdown);
            }
        }

        // And do the same for links
        $matches = array();
        if (preg_match_all('/<a *href *= *[\'|"]([^\'|"]*)/', $markdown, $matches)) {
            foreach ($matches[1] as $match) {
                $sMatch = str_replace(' ', '%20', $match);
                $markdown = str_replace($match, $sMatch, $markdown);
            }
        }

        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        // Retrieve the filename from the session
        $filename = $aeSession->get('filename', '');

        // Be sure to have the .md extension
        $filename = $aeFiles->removeExtension($filename).'.md';

        if ($filename !== '') {

            // Get the absolute filename on the disk
            $filename = $aeSettings->getFolderDocs(true).$filename;

            // And write the file
            $aeFiles = \MarkNotes\Files::getInstance();
            $aeFiles->rewriteFile($filename, $markdown);
        } else {

            /*<!-- build:debug -->*/
            if ($aeSettings->getDebugMode()) {
                $aeDebug->here('Event markdown.write - Session invalid, no filename found', 5);
            }
            /*<!-- endbuild -->*/
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('markdown.write', __CLASS__.'::writeMD');
        return true;
    }
}
