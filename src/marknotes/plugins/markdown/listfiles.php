<?php
/**
 * ListFiles : retrieve the list of files under a specific folder
 * This plugin will replace the tag '%LISTFILES myFolder' by a bullet
 * list with any files under the myFolder (and subfolder) folder.
 *
 * Just like if the user has manually introduce this bullet list
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class ListFiles extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.listfiles';
	protected static $json_options = '';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		// Check the presence of the LISTFILES tag
		if (preg_match_all('/%LISTFILES ([^\\%]*)%/', $params['markdown'], $matches)) {
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFolders = \MarkNotes\Folders::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();
			$aeSession = \MarkNotes\Session::getInstance();

			// Retrieve the note fullpath
			$root = str_replace('/', DS, dirname($params['filename'])).DS;

			list($arrTags, $arrFolders) = $matches;

			$i = 0;
			$j = count($arrFolders);

			for ($i == 0; $i < $j; $i++) {
				// Retrieve the full folder name like
				// C:\sites\notes\docs\folder\subfolder
				if (trim($arrFolders[$i]) == '.') {
					$folder = $root;
				} else {
					$folder = str_replace('/', DS, $arrFolders[$i]);
				}

				if (!($aeFolders->exists($folder))) {
					$folder = $root.$folder.DS;
				}

				if (!($aeFolders->exists($folder))) {
					// Correctly handle accentuated characters
					$folder = utf8_decode($folder);
				}

				if ($aeFolders->exists($folder)) {

					// Retrieve the list of files under that $folder
					//$arrFiles = $aeFiles->rglob('*', $folder);
					$arrFiles = $aeFolders->getContent($folder, true);

					// Do we need to encode accent on that system ?
					//$arr = $aeSettings->getPlugins('/files', //array('encode_accent'=>0));
					//$bEncodeAccents = boolval($arr['encode_accent']);

					$sList = '';

					$doc = $aeSettings->getFolderDocs(true);

					$root_url = $aeFunctions->getCurrentURL();

					foreach ($arrFiles as $file) {

						if ($file['type']=='file') {

							// Don't take files starting with a dot
							if (substr($file['basename'], 0, 1) !== '.') {

								$html = '';

								$fullname = $doc.str_replace('/', DS, $file['path']);

								$relURL = $aeSettings->getFolderDocs(false).$file['path'];
								$relURL = str_replace(DS, '/', $relURL);
								$filename = str_replace($root, '', $fullname);

								$url = str_replace(' ', '%20', $root_url.$relURL);

								if ($file['extension']=='md') {
									$html = ' ([html]('.str_replace('.md','.html', $url).')';

									$html .= ' |  [reveal]('.str_replace('.md','.reveal', $url).'))';
								}

								$sList .= "* [".$filename."](".str_replace(' ', '%20', $url).")".$html.PHP_EOL;
							}
						} // if ($aeFiles->exists($file))
					} // foreach ($arrFiles as $file)

					$params['markdown'] = str_replace($arrTags[$i], $sList, $params['markdown']);
				} // if ($aeFolders->exists($folder))
			} // for
		} // if (preg_match_all('/%LISTFILES

		return true;
	}
}
