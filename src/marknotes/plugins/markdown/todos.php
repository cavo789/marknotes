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
 *          "todos" {
 *             "introduction":"## Summary",
*              "todos" : {
 *                "pattern" : "(Todo)( |\a|\t|:){1}(.*)",
 *                "title": "### Todo's overview",
 *                "column": "Action point"
 *             },
 *             "decisions" : {
 *                "pattern" : "(Decision)( |\a|\t|:){1}(.*)",
 *                "title": "### Decision points",
 *                "column": "Decision"
 *             }
 *		   }
 *     }
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

        $aeFiles = \MarkNotes\Files::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();
        $aeSettings = \MarkNotes\Settings::getInstance();

		$task = $aeSession->get('task');

        $file = $aeSession->get('filename');
        $matches = array();

        $bIntroAdded = false;

        $url = rtrim($aeFunctions->getCurrentURL(false, false), '/');

        $urlHTML = '';
        if ($file !== '') {
            $urlHTML = $url.'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
            $urlHTML .= str_replace(DS, '/', $aeFiles->replaceExtension($file, 'html'));
            $urlHTML = str_replace(' ', '%20', $urlHTML);
        }

        // Retrieve the title for the section, from settings.json
        //
        // $arrSettings is an array like :
        //      "todos" : {
        //         "pattern" : "(Todo)( |\a|\t|:){1}(.*)",
        //         "title": "## Todo's overview"
        //      },
        //      "decisions" : {
        //         "pattern" : "(Decision)( |\a|\t|:){1}(.*)",
        //         "title": "## Decision points"
        //      }

        $arrSettings = $aeSettings->getPlugins('options', 'todos');

        foreach ($arrSettings as $key => $value) {

            // $prefix will f.i. contains "todos" or "decisions" i.e. the name of the key
            // from settings.json->plugins->options->todos

            $prefix = $key;

            // $pattern will contain the regex pattern
            $pattern = trim($value['pattern'] ?? '');

            // and $title the ... title for the summary table

            if ($pattern == '') {
                continue;
            }

            // and $title the ... title for the summary table
            $title = trim($value['title'] ?? '');

            // and $column the text to use as header of the table's column
            $column = trim($value['column'] ?? '');

            if (preg_match_all("/".$pattern."/im", $params['markdown'], $matches)) {
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

                    $sWhoWhat = $matches[2][$i].($matches[3][$i] ?? '');

					if (!in_array($task, array("pdf","txt"))) {
						$anchor = "<a name='".$prefix."_".($i + 1)."'></a>";
					} else {
						$anchor = "";
					}

                    $sTodo = $anchor."**".$matches[1][$i].' #'.($i + 1).$sWhoWhat."**";

                    $params['markdown'] = str_replace($matches[0][$i], $sTodo, $params['markdown']);
                    $arrTodos[$i] = $sWhoWhat;
                }

                // ----------------------------------------------------
                // Add the Todo summary after the content

                $sTodo = '';

                if ($bIntroAdded == false) {
                    $introduction = $arrSettings['introduction'] ?? '';

                    if (trim($introduction) !== '') {
                        $bIntroAdded = true;

                        $sTodo .= trim($introduction).PHP_EOL.PHP_EOL;
                    }
                }

                $sTodo .= $title.PHP_EOL.PHP_EOL."| ID | ".$column." |".PHP_EOL."| --- | --- |".PHP_EOL;

                foreach ($arrTodos as $key => $value) {

					if (!in_array($task, array("pdf","txt"))) {
						$anchor="[".($key + 1)."](".$urlHTML."#".$prefix."_".($key + 1).")";
					} else {
						 $anchor=$key + 1;
					 }

                    $sTodo .= "| ".$anchor." | ".$value." |".PHP_EOL;
                }
                $sTodo .= PHP_EOL.PHP_EOL;

                $params['markdown'] .= PHP_EOL.$sTodo;
            }
        } // foreach ($arrSettings as $key => $value)

        return true;
    }

    /**
     * Attach the function and responds to events
     */
    public function bind()
    {
        $aeEvents = \MarkNotes\Events::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        $task = $aeSession->get('task');

        // Don't fire this plugin when the task is edit.form or during a search
        if (in_array($task, array('edit.form','search'))) {
            return true;
        }

        $aeEvents->bind('markdown.read', __CLASS__.'::readMD');
        return true;
    }
}
