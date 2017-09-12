<?php

/**
 * Analyze the markdown content; once read, and replace any html img tag by the
 * correct markdown syntax.
 *
 * So replace <img src="image1.png" width="133" height="24" /> by
 * ![](image1.png "133x24").
 *
 * "133x24" take the place of the title attribute (standard syntax in Markdown). This
 * because Marknotes will retrieve this "title" and detect that it's a width x height and,
 * therefore, will give a size to the image
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Images
{

    public static function readMD(&$params = null)
    {

        if (trim($params['markdown']) === '') {
            return true;
        }

		$pattern = '/\<img src=[\'"]([^\'"]*)[\'"] width=[\'"]([^\'"]*)[\'"] height=[\'"]([^\'"]*)[\'"] \/\>/';

		if (preg_match_all($pattern, $params['markdown'], $matches)) {


			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSession = \MarkNotes\Session::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();

			$i=0;

			for($i=0; $i<count($matches[0]); $i++) {

				// $tag    => $matches[0][0] will be f.i. "<img src="image1.png" width="133" height="24" />"
				// $img    => $matches[1][0] will be f.i. "image1.png"
				// $width  => $matches[2][0] will be f.i. "133"
				// $height => $matches[3][0] will be f.i. "24"

				list($tag, $img, $width, $height) = $matches;

				$img = '![]('.$img[$i].' "'.$width[$i].'x'.$height[$i].'")';

				$params['markdown']=str_replace($tag[$i], $img, $params['markdown']);

			} // for

			// Rewrite the file on the disk so remove img tags

			$sURL=$aeFunctions->getCurrentURL();
			$sURL.=str_replace(DS, '/', dirname($aeSettings->getFolderDocs(false).$aeSession->get('filename'))).'/';
			$sURL=str_replace(' ', '%20', $sURL);

			$sContent=str_replace($sURL, '%URL%', $params['markdown']);

			// Replace links to the folder where the note resides by the
			// %NOTE_FOLDER% variable
			$folder=rtrim(str_replace('/', DS, dirname($params['filename'])), DS);
			$sContent = str_replace($folder.DS, '%NOTE_FOLDER%', $sContent);
			$sContent = str_replace($folder, '%NOTE_FOLDER%', $sContent);

			$aeEvents = \MarkNotes\Events::getInstance();
	        $aeEvents->loadPlugins('markdown');
	        $args = array(&$sContent);
	        $aeEvents->trigger('markdown.write', $args);
	        $sContent = $args[0];

			// In the form, keep the %URL% variable and not the full path
			// to the image
			if ($aeSession->get('task', '')=='edit.form') {
				$params['markdown']=$sContent;
			}

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

		$task=$aeSession->get('task', '');

		if(!in_array($task, array('search'))) {
			$aeEvents->bind('markdown.read', __CLASS__.'::readMD');
		}

		return true;
	}
}
