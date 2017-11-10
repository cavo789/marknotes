<?php

/**
 * This plugin will scan the markdown content and check for the presence
 * of the "%TODOS%" tag. If found, try to retrieve every "Todo" like in
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

class Todos extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.todos';
	protected static $json_options = 'plugins.options.markdown.todos';

	/**
	 * Scan the markdown content, search every TODO occurences and generate
	 * an array with the list of todos
	 */
	private static function getTodos(string $markdown) : array
	{
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

		$aeRegex = \MarkNotes\Helpers\Regex::getInstance();

		$arrOptions = self::getOptions('tags', array());

		$sTodo = '';

		for ($i=0; $i < count($arrOptions); $i++) {


			$tmp = $aeRegex->removeMarkdownCodeBlock($markdown);

			// Get the pattern f.i. (Todo)( |\\a|\\t|:){1}(.*)"
			$pattern = trim($arrOptions[$i]['pattern']);

			if ($pattern == '') {
				continue;
			}

			if (preg_match_all("/".$pattern."/im", $tmp, $matches)) {
				// Get the title, column and anchor
				// For instance,
				//   "title": "### Todo's overview"
				//   "column": "Action point"
				//   "anchor": "todo"
				$title = trim($arrOptions[$i]['title']);
				$column = trim($arrOptions[$i]['column']);
				$anchor = trim($arrOptions[$i]['anchor']);

				$arrTodos = array();

				// Get the number of groups in the regex
				//
				// Example : "Todo Christophe : make it rocks!"
				//
				// 0 : the matched string    => "Todo Christophe : make it rocks!"
				// 1 : The Todo word         => "Todo"
				// 2 : Spaces or punctuation just after the word => ""
				// 3 : After the punctuation => "Christophe : make it rocks!"

				$entries = count($matches[0]);

				for ($j = 0; $j < $entries; $j++) {
					// Prepare the new line :
					//   * Add a todo number (given by #$j)
					//   * Add put the line in bold

					$sWhoWhat = $matches[2][$j].($matches[3][$j] ?? '');

					if (!in_array($task, array("pdf","txt"))) {
						$link = "<a name='".$anchor."_".($j + 1)."'></a>";
					} else {
						$link = "";
					}

					$tmp = $link."**".$matches[1][$j].' #'.($j + 1).$sWhoWhat."**";

					$markdown = str_replace($matches[0][$j], $tmp, $markdown);
					$arrTodos[$j] = $sWhoWhat;
				}

				// ----------------------------------------------------
				// Add the Todo summary after the content

				if ($bIntroAdded == false) {
					$introduction = trim(self::getOptions('introduction', ''));

					if (trim($introduction) !== '') {
						$bIntroAdded = true;
						$sTodo .= PHP_EOL.PHP_EOL.trim($introduction);
					}
				}

				$sTodo .= PHP_EOL.PHP_EOL.$title.PHP_EOL.PHP_EOL."| ID | ".$column." |".PHP_EOL."| --- | --- |".PHP_EOL;

				foreach ($arrTodos as $key => $value) {
					if (!in_array($task, array("pdf","txt"))) {
						$link="[".($key + 1)."](#".$anchor."_".($key + 1).")";
					} else {
						 $link=$key + 1;
					}

					$sTodo .= "| ".$link." | ".trim($value, " :,!;")." |".PHP_EOL;
				} // foreach ($arrTodos
			} // if (preg_match_all("/".$pattern."/im"
		} // foreach ($arrOptions as $key => $value)

		return array($markdown, trim($sTodo));
	}

	/**
	 * The markdown file has been read, this function will get the content of the .md file and
	 * make some processing like data cleansing
	 *
	 * $params is a associative array with, as entries,
	 *		* markdown : the markdown string (content of the file)
	 *		* filename : the absolute filename on disk
	 */
	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		// The todos plugins only fires when the %TODOS% tag has been found
		// in the note's content.
		if (preg_match_all('/%TODOS%/', $params['markdown'], $matches)) {
			list($params['markdown'], $todos) = self::getTodos($params['markdown']);
			$params['markdown']=str_replace($matches[0], $todos, $params['markdown']);
		}

		return true;
	}
}
