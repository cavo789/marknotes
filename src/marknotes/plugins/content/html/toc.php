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
     * Modify the HTML rendering of the note
     */
    public static function doIt(&$content = null)
    {
        if (trim($content) === '') {
            return true;
        }

        // Try to find the tag : %TOC_9%  (where 9 is the deepest level to mention
        // in the table of content (so, for headings 1 -> 4, mention %TOC_4%)

		if (preg_match("/%TOC_(\\d)%/", $content, $match)) {

            $aeSettings = \MarkNotes\Settings::getInstance();

            // Get the deepest level
            $deepestLevel = (int)$match[1];

            // Retrieve every h2 till the lowest level (f.i. 4)
            $pattern = '/<h([2-'.$deepestLevel.']){1} *(id="(.*)")?[^>]*>(.*)<\/h[2-'.$deepestLevel.']>/i';

            if (preg_match_all($pattern, $content, $matches)) {

				$aeFunctions = \MarkNotes\Functions::getInstance();

				list($tags, $level, $id, $slug, $title) = $matches;

	            // Retrieve the title for the section, from settings.json
	            $arrSettings = $aeSettings->getPlugins('options', 'toc');

	            $text = $arrSettings['text'] ?? "**Table of content** : %s";

				// $text is probably written in the markdown language, get html version
				$file=$aeSettings->getFolderLibs()."parsedown/Parsedown.php";

				if (is_file($file)) {
		           include_once $aeSettings->getFolderLibs()."parsedown/Parsedown.php";
				   $parsedown = new \Parsedown();
				   $text=$parsedown->text(trim($text));
			   	}

			   	// Just add a carriage return after each entries
				$heads = implode("\n", $matches[0]);

				$j = count($matches[0]);

				// Process every entries in the table of content
				for ($i = 0; $i < $j; $i++) {

					/*<!-- build:debug -->*/
					// When the developper mode is enabled in settings.json, the
					// INCLUDE plugin will add a sentence like
					//
					//   ###### DEV_MODE_PREFIX INCLUDE FILE filename {.devmode}
					//
					// (DEV_MODE_PREFIX is a prefiw defined in includes/constants.php)
					//
					// So, here in the Table of Content plugin, we should ignore
					// headings when the title starts with the DEV_MODE_PREFIX and
					// don't put them in the table of content.
					if ($aeFunctions::startsWith($title[$i],DEV_MODE_PREFIX)) {
						$heads = str_replace($matches[0][$i], '', $heads);
						continue;
					}
					/*<!-- endbuild -->*/

					$entry = '<li class="toc'.$level[$i].'"><a href="#'.$slug[$i].'">'.$title[$i].'</a></li>';

					// Replace the header by the entry
					$heads = str_replace($matches[0][$i], $entry, $heads);
				}

                // Put everything in a navigation element
                $heads = "<nav role='navigation' id='toc'><ul>\n".$heads."\n</ul></nav>";

                // And replace the tag (%TOC_3% f.i.) by the table of content
				$text = sprintf($text, $heads);
                $content = str_replace('<p>'.$match[0].'</p>', $text, $content);

            } // if (preg_match_all($pattern

        } // if (preg_match("/%TOC_(\\d)%/"

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

        if (in_array($task, array('edit.form','pdf','remarks','reveal','search','slides'))) {
            return false;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        $aeEvents->bind('render.content', __CLASS__.'::doIt');
        return true;
    }
}
