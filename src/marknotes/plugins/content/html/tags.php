<?php

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Tags
{
    public static function addTags(string &$content = '') : bool
    {
        // Can be empty when no note is being displayed
        if (trim($content) === '') {
            return true;
        }

        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task');

        // Tags are only for the interface i.e. when the task is "display"
        if ($task !== 'display') {
            return true;
        }

        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFiles = \MarkNotes\Files::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $arrSettings = $aeSettings->getPlugins('options', 'tags');
        $sPrefix = $arrSettings['prefix'] ?? '§';
        $msg = $aeSettings->getText('apply_filter_tag', 'Display notes containing this tag', true);

        // Check if the file contains words present in the tags.json file : if the file being displayed
        // contains a word (f.i. "javascript") that is in the tags.json (so it's a known tag) and that
        // word is not prefixed by the "§" sign add it : transform the "plain text" word and add the "tag" prefix

        if ($aeFiles->fileExists($fname = $aeSettings->getFolderWebRoot().'tags.json')) {
            if (filesize($fname) > 0) {
                $aeJSON = \MarkNotes\JSON::getInstance();

                $arrTags = $aeJSON->json_decode($fname);

                foreach ($arrTags as $tag) {
                    // For each tag, try to find the word in the markdown file

                    // /( |\\n|\\r|\\t)+               Before the tag, allowed : space, carriage return, linefeed or tab
                    // [^`\/\\#_\-§]?                  Before the tag, not allowed : `, /, \, #, -, _ and § (the PREFIX_TAG)
                    // ('.preg_quote($tag).')          The tag term (f.i. "javascript"
                    // (\\n|,|;|\\.|\\)|[[:blank:]]|$) After the tag, allowed : carriage return, comma, dot comma, dot, ending ), tag or space or end of line

                    // Capture the full line (.* ---Full Regex--- .*)
                    preg_match_all('/(.*( |\\n|\\r|\\t|\\*|\\#)+('.preg_quote($tag).')(\\n|,|;|\\.|\\)|\\t|\\*|\\#| |$)*)/i', $content, $matches);

//@TODO : il y a un souci ave la regex qui ne devrait pas retrouver des
//mots qui sont dans des attributs.  title="marknotes" ==> marknotes ne devrait
//pas être matchés. Voir le plugin SEO qui fonctionne et qui exclu lui les
//attributs.

                    foreach ($matches[0] as $match) {
                        if (count($match) > 0) {
                            preg_match('/(.*( |\\n|\\r|\\t|\\*|\\#)+('.preg_quote($tag).')(?!([^<]+)?>)(\\n|,|;|\\.|\\)|\\t|\\*|\\#| |$).*)/i', $match, $matches);

                            // The found tag is : $matches[3]
                            /*<!-- build:debug -->*/
                            /*if ($aeSettings->getDevMode()) {
                                $aeDebug->here('Found tag '.$matches[3], 1);
                            }*/
                            /*<!-- endbuild -->*/

                            // Replace, in the line, the word f.i.    (don't use a preg_replace because preg_replace will replace all occurences of the word)

                            //   Line  : Start a SSH connexion     (original)
                            //   By    : Start a §SSH connexion    (new line)

                            // $matches[2] : what was just before the tag      f.i.   " Start a SSH, then ..."  => the space before SSH
                            // $matches[3] : the tag                                  " Start a SSH, then ..."  => SSH
                            // $matches[4] : what was just after the tag              " Start a SSH, then ..."  => the comma after SSH

                            $sLine = str_ireplace($matches[2].$matches[3].$matches[4], $matches[2].'<span class="tag" title="'.$msg.'" data-task="fnPluginContentTag" data-tag="'.$matches[3].'">'.$matches[3].'</span>'.$matches[4], $matches[0]);

                            // And now, replace the original line ($matches[0]) by the new one in the document.

                            $content = str_replace($matches[0], $sLine, $content);
                        } // if (count($match)>0)
                    } // foreach ($matches[0] as $match)
                } // foreach
            } // if(filesize($fname)>0)
        } // if ($aeFiles->fileExists($fname=$this->_rootFolder.'tags.json'))

        return true;
    }

    /**
     * Provide additionnal css
     */
    public static function addCSS(&$css = null)
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $css .= "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" href=\"".$root."/marknotes/plugins/content/html/tags/tags.css\" />\n";

        return true;
    }

    /**
     * Provide additionnal javascript
     */
    public static function addJS(&$js = null) : bool
    {
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $arrSettings = $aeSettings->getPlugins('options', 'tags');

        $root = rtrim($aeFunctions->getCurrentURL(true, false), '/');

        $prefix = $arrSettings['prefix'] ?? '§';

        $tags = '';
        if (isset($arrSettings['auto_select'])) {
            $tags = implode($arrSettings['auto_select'], ",");
        }

        if ($aeSettings->getDebugMode()) {
            $js .= "\n<!-- Lines below are added by ".__FILE__."-->";
        }

        $js .=
            "\n<script type=\"text/javascript\">\n".
            "marknotes.plugins.tags = {};\n".
            "marknotes.plugins.tags.prefix='".$prefix."';\n".
            "marknotes.plugins.tags.auto_tags='".$tags."';\n".
            "</script>\n";

        $js .= "<script type=\"text/javascript\" src=\"".$root."/marknotes/plugins/content/html/tags/tags.js\"></script>\n";

        if ($aeSettings->getDebugMode()) {
            $js .= "<!-- End for ".__FILE__."-->";
        }

        return true;
    }

    /**
     * Remove tags prefix (by default §) in case of this character has been manually added by the document author
     */
    public static function showHTML(&$html) : bool
    {
        if (trim($html) === '') {
            return true;
        }

        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task');

        // Tags are only for the interface i.e. when the task is "display"
        if ($task !== 'display') {
            $aeSettings = \MarkNotes\Settings::getInstance();

            $arrSettings = $aeSettings->getPlugins('options', 'tags');
            $prefix = $arrSettings['prefix'] ?? '§';

            // Don't keep the § (tags prefix) for slideshow
            $html = str_replace($prefix, '', $html);
            $html = str_replace(htmlentities($prefix), '', $html);  // &sect; = §
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind() : bool
    {

        /*<!-- build:debug -->*/
        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();
        if ($aeSettings->getDevMode()) {
            $aeDebug->log('Plugin tag disabled, the REGEX should be reviewed; see @TODO', 'warning');
        }
        return false;
        /*<!-- endbuild -->*/


        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.css', __CLASS__.'::addCSS');
        $aeEvents->bind('render.js', __CLASS__.'::addJS');
        $aeEvents->bind('render.content', __CLASS__.'::addTags');
        $aeEvents->bind('display.html', __CLASS__.'::showHTML');
        return true;
    }
}
