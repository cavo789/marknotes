<?php

/**
 * When exporting a note to a PDF file, the %TOC_99% tag (=insert a table of
 * content) shouldn't be interpreted since the pandoc converter already add such
 * table so just remove the tag
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class TOC
{
    /**
     * The markdown file has been read, this function will get the content of the .md file and
     * make some processing like data cleansing
     *
     * $params is a associative array with, as entries,
     *		* markdown : the markdown string (content of the file)
     *		* filename : the absolute filename on disk
     */
    public static function readMD(&$params = null)
    {

        if (trim($params['markdown']) === '') {
            return true;
        }

        if (preg_match("/%TOC_(\\d)%/", $params['markdown'], $match)) {

            $aeSettings = \MarkNotes\Settings::getInstance();

            // And replace the tag (%TOC_3% f.i.) by the table of content
            $params['markdown'] = str_replace($match[0], '', $params['markdown']);

		}

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        // Fire this plugin only for the specified task
        if (!in_array($aeSession->get('task'), array('pdf','txt'))) {
            return false;
        }

        $aeEvents->bind('markdown.read', __CLASS__.'::readMD');
        return true;
    }
}
