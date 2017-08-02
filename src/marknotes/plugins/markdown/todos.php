<?php

/**
 * This plugin will scan the markdown content and retrieve every "Todo" like in
 *
 *   Ipso lorem
 *   Todo Christophe : Check if i's possible to ...
 *
 * Once sentence with "Todo" has been found, every todos will be numeroted (starting at 1).
 * Then a Todos overview table will be added in the document with, for every IDs, a link to the
 * place in the document with that todo.
 *
 * In settings.json, you can change a few settings like the title of the added section :
 *
 * "plugins": {
 *		"options": {
 *			"todos": {
 *	 			"title": "### Hey guys, this is my Todo's overview"
 *			}
 *		}
 *	}
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Todos
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

        // Don't fire this plugin when the task is edit.form
        if (in_array($params['task'], array('edit.form'))) {
            return true;
        }

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

        $file = $aeSession->get('filename');
        $matches = array();

        $url = rtrim($aeFunctions->getCurrentURL(false, false), '/');

        $urlHTML = '';
        if ($file !== '') {
            $urlHTML = $url.'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
            $urlHTML .= str_replace(DS, '/', $aeFiles->replaceExtension($file, 'html'));
            $urlHTML = str_replace(' ', '%20', $urlHTML);
        }

        // Search strings starting with "Todo" followed or not by a space or a :
        if (preg_match_all("/(Todo)([[:blank:]]?:?)(.*)/im", $params['markdown'], $matches)) {
            $arrTodos = array();

            // Get the number of groups in the regex
            //
            // Example : "Todo Christophe : make it rocks!"
            //
            // 0 : the matched string                        => "Todo Christophe : make it rocks!"
            // 1 : The todo word                             => "Todo"
            // 2 : Spaces or punctuation just after the word => ""
            // 3 : After the punctuation                     => "Christophe : make it rocks!"

            $j = count($matches);

            $entries = count($matches[0]);

            for ($i = 0; $i < $entries; $i++) {

                // Prepare the new line :
                //   * Add a todo number (given by #$i)
                //   * Add put the line in bold

                $sWhoWhat = $matches[2][$i].$matches[3][$i];
                $sTodo = "<a name='todo_".($i + 1)."'></a> [ ] ".$matches[1][$i].' #'.($i + 1).$sWhoWhat;

                $params['markdown'] = str_replace($matches[0][$i], '**'.$sTodo.'**', $params['markdown']);
                $arrTodos[$i] = $sWhoWhat;
            }

            // Add the Todo summary after the content

            // Retrieve the title for the section, from settings.json
            $arrSettings = $aeSettings->getPlugins('options', 'todos');
            $sTodo = $arrSettings['title'] ?? "### Todos Overview";

            $sTodo .= "\n| # | Todo |\n| --- | --- |\n";

            foreach ($arrTodos as $key => $value) {
                $sTodo .= "| [".($key + 1)."](".$urlHTML."#todo_".($key + 1).") | ".$value." |\n";
            }

            $params['markdown'] .= $sTodo;
        }

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeEvents->bind('markdown.read', __CLASS__.'::readMD');
        return true;
    }
}
