<?php
/**
 * Add an image galery but, not when the task is export.html
 * (there is a content HTML plugin for that purpose) but when
 * the task is task.export.reveal because, then, the images are
 * not retrieved by the content HTML plugin
 *
 * Find any '%GALLERY images_folder%' and get the list of images in
 * that folder, then insert <section data-background=""> tags.
 */
namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class Image_Gallery extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.image_gallery';
	protected static $json_options = 'plugins.options.markdown.image_gallery';

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$matches = array();

		// Check if the content contains things like ' %GALLERY .images/folder/demo%'
		// i.e. '%GALLERY ' followed by a foldername and ending by '%'

		if (preg_match_all('/%GALLERY ([^\\%]*)%/', $params['markdown'], $matches)) {
			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFolders = \MarkNotes\Folders::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();
			$aeSession = \MarkNotes\Session::getInstance();

			// Retrieve the note fullpath
			$root = rtrim($aeSettings->getFolderDocs(true), DS).DS;
			$root = $root.dirname($aeSession->get('filename')).DS;

			$arrTags = $matches[0];
			$arrFolders = $matches[1];

			$j = count($arrFolders);

			for ($i = 0; $i < $j; $i++) {
				// Retrieve the full folder name like
				// C:\sites\notes\docs\folder\subfolder\.images
				//$folder = str_replace('/', DS, $root.$arrFolders[$i]);

				// Retrieve the full folder name like
				// C:\sites\notes\docs\folder\subfolder
				if (trim($arrFolders[$i]) == '.') {
					$folder = '';
				} else {
					$folder = str_replace('/', DS, $arrFolders[$i]);
				}

				if (!($aeFolders->exists(realpath($root.$folder)))) {
					// Correctly handle accentuated characters
					$folder = utf8_decode($folder);
				}

				if ($aeFolders->exists(realpath($root.$folder))) {
					// image folder
					$imgFolder = realpath($root.$folder);
					$imgFolder = str_replace($aeSettings->getFolderDocs(true), '', $imgFolder);

					// Retrieve the note URL
					$url = rtrim($aeFunctions->getCurrentURL(), '/');
					$url .= '/'.$aeSettings->getFolderDocs(false);
					$url .= $imgFolder.'/';
					$url = str_replace(DS, '/', $url);

					$arrFiles = $aeFiles->rglob('*', $root.$folder);

					$images = '';

					foreach ($arrFiles as $file) {
						$ext = strtolower($aeFiles->getExtension($file));

						if (in_array($ext, array('gif','jpg','jpeg','png','svg','webp'))) {
							$file = utf8_encode($file);
							$file = str_replace($root, '', $file);

							$file = str_replace(DS, '/', $file);
							$img = '!['.basename($file).']('.$url.basename($file).')';
							$images .= "\n---\n".$img."\n";
						}
					}

					$params['markdown'] = str_replace($arrTags[$i], $images, $params['markdown']);
				}
			}
		}
		return true;
	}
}
