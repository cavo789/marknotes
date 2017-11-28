<?php
/**
 * Add a gallery of images in a HTML page.
 *
 * Find any '%GALLERY images_folder%' and get the list of imaages in that folder, then
 * insert <img> tags.
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Image_Gallery extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.image_gallery';
	protected static $json_options = 'plugins.options.content.html.image_gallery';

	protected static $arrImg = array('gif','jpg','jpeg','png','svg','webp');

	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}


		// Don't keep unwanted HTML tags
		$arrNotIn = self::getOptions('not_in_tags', array('code','pre'));
		$aeRegex = \MarkNotes\Helpers\Regex::getInstance();
		$tmp = $aeRegex->removeTags($content, $arrNotIn);

		// Check if the content contains things like '%GALLERY .images/folder/demo%'
		// i.e. '%GALLERY ' followed by a foldername and ending by '%'

		$pattern = '/%GALLERY ([^\\%]*)%/';

		if (preg_match_all($pattern, $content, $matches)) {

			// What should happens when the user click on a thumbnail ?
			// Open in a modal or in a new tab ?
			$open_in_modal = boolval(self::getOptions('open_in_modal', 1));

			$aeSession = \MarkNotes\Session::getInstance();
			$task = $aeSession->get('task', '');

			$aeFiles = \MarkNotes\Files::getInstance();
			$aeFunctions = \MarkNotes\Functions::getInstance();
			$aeSettings = \MarkNotes\Settings::getInstance();
			$aeSession = \MarkNotes\Session::getInstance();

			// Retrieve the note fullpath
			$root = rtrim($aeSettings->getFolderDocs(true), DS).DS;
			$root = $root.dirname($aeSession->get('filename')).DS;

			$arrTags = $matches[0];
			$arrFolders = $matches[1];

			$i = 0;
			$j = count($arrFolders);

			for ($i == 0; $i < $j; $i++) {
				if (trim($arrFolders[$i]) == '.') {
					$folder = '';
				} else {
					$folder = str_replace('/', DS, $arrFolders[$i]);
				}

				if (!(is_dir(realpath($root.$folder)))) {
					// Correctly handle accentuated characters
					$folder = utf8_decode($folder);
				}

				if (is_dir(realpath($root.$folder))) {
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

						if (in_array($ext, static::$arrImg)) {
							$file = utf8_encode($file);
							$file = str_replace($root, '', $file);
							$file = str_replace(DS, '/', $file);

							$alt = $aeFiles->removeExtension(basename($file));

							$img = '<img alt="'.$alt.'" src="'.$url.basename($file).'"/>';

							$img='<a title="'.$alt.'" href="'.$url.basename($file).'" data-modal="'.$open_in_modal.'">'.$img.'</a>';

							if (in_array($task, array('task.export.pdf', 'task.export.reveal'))) {
								// Add the image in a section so this
								// will create a new page in the
								// pdf document
								$images .= '<section>'.$img.'</section>';
							} else {
								$images .= '<div>'.$img.'</div>';
							}
						}
					} // foreach

					if (!(in_array($task, array('pdf', 'reveal')))) {
						$images = '<div id="image_gallery">'.$images.'</div>';
					}

					$content = str_replace($arrTags[$i], $images, $content);
				} // if (is_dir(realpath($root.$folder)))
			} // for ($i == 0; $i < $j; $i++)
		} // if (preg_match_all($pattern, $content, $matches))

		return true;
	}
}
