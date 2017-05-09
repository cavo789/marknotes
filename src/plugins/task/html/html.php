<?php

/**
 * What are the actions to fired when MarkNotes is running the "html" task ?
 */

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

class HTML
{
    public static function run(&$params = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        // Display the HTML rendering of a note
        $aeTask = \MarkNotes\Tasks\Display::getInstance();
        header('Content-Type: text/html; charset=utf-8');
        $html = $aeTask->run($params);

        // Retrieve headings
        $matches = array();
        preg_match_all('|<h[^>]+>(.*)</h[^>]+>|iU', $html, $matches);

        // $matches contains the list of titles (including the tag so f.i. "<h2>Title</h2>"
        foreach ($matches[0] as $tmp) {

            // In order to have nice URLs, extract the title (stored in $tmp)
            // $tmp is equal, f.i., to <h2>My slide title</h2>
            $id = $aeFunctions->slugify(strip_tags($tmp));

            // The ID can't start with a figure, remove it if any
            // Remove also . - , ; if present at the beginning of the id
            $id = preg_replace("/^[\d|.|\-|,|;]+/", "", $id);

            // The tag (like h2)
            $head = substr($tmp, 1, 2);

            $html = str_replace($tmp, '<'.$head.' id="'.$id.'">'.strip_tags($tmp).'</'.$head.'>', $html);
        }

        echo $html;

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('run.task', __CLASS__.'::run');
        return true;
    }
}
