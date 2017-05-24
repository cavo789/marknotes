<?php
/**
 * Add extra security on the produced content like adding the rel=noopener attribute to links
 */

namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Security
{

    /**
     * Add a rel="noopener" attribute to any URLs
     * (see https://mathiasbynens.github.io/rel-noopener/)
     */
     private static function addRelNoOpener(string $content = null) : string
     {
         // Extract any <a> tags in the HTML content
         // For instance <a href="https://www.marknotes.fr">MarkNotes</a>
         // and explode the links in an array like this :
         //
         // [0] => <a href="https://www.marknotes.fr">MarkNotes</a>  (entire tag)
         // [1] => href="https://www.marknotes.fr"                   (attributes of <a xxxx>)
         // [2] => MarkNotes                                         (the caption)
         //
         // And add the rel="noopener" attribute and "noreferrer" for old browsers

         preg_match_all("/<a ([^\>]*)>(.*)<\/a>/siU", $content, $matches, PREG_SET_ORDER);

         foreach ($matches as $match) {
             $content = str_replace(
                 $match[0],
                 '<a '.$match[1].' rel="noopener noreferrer">'.$match[2].'</a>',
                 $content
             );
         }

         return $content;
     }

    /**
     * Modify the HTML rendering of the note
     */
    public static function doIt(&$content = null)
    {
        if (trim($content) === '') {
            return true;
        }

        $content = self::addRelNoOpener($content);

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeSession = \MarkNotes\Session::getInstance();
        $task = $aeSession->get('task', '');

        // This plugin is needed only for these tasks : main, display and html

        if (!in_array($task, array('main', 'display', 'html'))) {
            return true;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('render.content', __CLASS__.'::doIt');
        return true;
    }
}
