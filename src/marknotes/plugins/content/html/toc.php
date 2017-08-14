<?php
/**
 * This plugin will add a table of content in your html document (i.e. once the markdown
 * note has been converted in a HTML document)
 *
 * Just add a tag like %TOC_5% in your markdown note to tell : take every headings 2 till 5
 * (included), generate a table of content (toc) and replace the tag by the toc.
 *
 * This plugin doesn't have options to set in settings.json
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class TOC
{

    /**
     * Get the list of headings (h1, h2, h3, ...), extract the text
     * (f.i. <h2>Title</h2> => get Title), and derived an "id" thanks to slugify function.
     * Add then <h2 id="title">Title</h2>
     *
     * This way, a nice table of contents can be proposed f.i.
     */
     private static function addIdToHeadings(string $html) : string
     {
         $aeFunctions = \MarkNotes\Functions::getInstance();

        // Retrieve headings
        $matches = array();
         preg_match_all('|<h\d{1}[^>]+>(.*)</h\d{1}[^>]+>|iU', $html, $matches);

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

         return $html;
     }

    /**
     * Modify the HTML rendering of the note
     */
    public static function doIt(&$content = null)
    {
        if (trim($content) === '') {
            return true;
        }

        // First, add nice IDs to every header
        $content = self::addIdToHeadings($content);

        // Try to find the tag : %TOC_9%  (where 9 is the deepest level to mention
        // in the table of content (so, for headings 1 -> 4, mention %TOC_4%)

        if (preg_match("/%TOC_(\\d)%/", $content, $match)) {

            $aeSettings = \MarkNotes\Settings::getInstance();

            // Get the deepest level
            $deepestLevel = (int)$match[1];

            // Retrieve the title for the section, from settings.json
            $arrSettings = $aeSettings->getPlugins('options', 'toc');
            $sTitle = $arrSettings['title'] ?? "### Table of content";

            // Retrieve every h2 till the lowest level (f.i. 4)
            $pattern = '/<h([2-'.$deepestLevel.']){1} *(id="(.*)")?[^>]*>(.*)<\/h[2-'.$deepestLevel.']>/i';

            if (preg_match_all($pattern, $content, $matches)) {

                // Just add a carriage return after each entries
                $heads = implode("\n", $matches[0]);

                $j = count($matches[0]);

                // Process every entries in the table of content
                for ($i = 0; $i < $j; $i++) {

                    // Build the entry in the TOC

                    // $matches[1][$i] is the level (f.i. 3 for a H3 tag)
                    // $matches[3][$i] is the "id=" value (f.i. "agenda_of_the_meeting")
                    // $matches[4][$i] is the text (f.i. "Agenda of the meeting")

                    $entry = '<li class="toc'.$matches[1][$i].'"><a href="#'.$matches[3][$i].'">'.$matches[4][$i].'</a></li>';

                    // Replace the header by the entry
                    $heads = str_replace($matches[0][$i], $entry, $heads);
                }

                // Put everything in a navigation element
                $heads = "<nav role='navigation' id='toc'><ul>\n".$heads."\n</ul></nav>";

                // And replace the tag (%TOC_3% f.i.) by the table of content
                $content = str_replace('<p>'.$match[0].'</p>', $heads, $content);
            }
        }

        return true;
    }

    /**
     * Provide additionnal css
     */
    public static function addCSS(&$css = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/marknotes/plugins/content/html/toc/toc.css\" />\n";

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // This plugin is not needed when the task is f.i. 'pdf'

        if (in_array($task, array('edit.form','pdf','search'))) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        $aeEvents->bind('render.content', __CLASS__.'::doIt');
        return true;
    }
}
