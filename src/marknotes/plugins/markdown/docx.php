<?php

/**
 * When exporting to docx, make a few cleaning and special processing
 *
 * After each images, this JSON will be added : {#id .class
 * width=999 height=999}
 *
 * For instance : ![](image1.png "133x24"){#id .class width=133 height=24}
 *
 * @link http://pandoc.org/MANUAL.html#extension-link_attributes
 */

namespace MarkNotes\Plugins\Markdown;

defined('_MARKNOTES') or die('No direct access allowed');

class DOCX extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.docx';
	protected static $json_options = 'plugins.options.markdown.docx';

	/**
	 * Add image's width / height information after the image link
	 * @link http://pandoc.org/MANUAL.html#extension-link_attributes
	 */
	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// When exporting to a .docx file, don't keep the encrypt tag
		$params['markdown'] = str_replace(ENCRYPT_MARKDOWN_TAG, '', $params['markdown']);

		// The code below will generate an #id tag. But,
		// since we'll have more than one image, use a counter.
		// Not a dummy or random figure, just a counter (meaningless)
		try {
			$wID=intval($aeSession->get('img_id', 0));
		} catch (Exception $e) {
			$wID=0;
		}

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log('Add image width extra info for Pandoc exportation', 'debug');
		}
		/*<!-- endbuild -->*/

		// Try to match things like ![img_ALT](http://site/my_image.png
		// "32x20"). The last part is optionnal and if mentionned
		// if a width followed by a height
		$pattern='/(\\!\\[.*](.* [\'"](\\d+|\\*)x(\\d+|\\*)[\'"]\)))(.*)/';

		if (preg_match_all($pattern, $params['markdown'], $matches)) {
			// $full the full string like ![img_ALT](http://site/my_image.png "32x20") ipso lorem
			// $tag will be the ![img_ALT](http://site/my_image.png "32x20")
			// $img will be set to (http://site/my_image.png "32x20")
			// $width will be set to "32"
			// $height will be set to "20"
			// $after will be set to what follow the image declaration. (f.i. ipso lorem)
			list($full, $tag, $img, $width, $height, $after) = $matches;

			$i=0;
			$aeDebug = \MarkNotes\Debug::getInstance();

			for ($i=0; $i<count($matches[0]); $i++) {
				$bDoIt=true;

				if ($after[$i]!=='') {
					// If something follow the image declaration and,
					// if that thing starts with '{#id' don't continue since
					// the image has already been processed. This is the
					// case When we're working with a master document and
					// included onces (i.e. with the %INCLUDE% tag)
					$bDoIt = (substr($after[$i], 0, 4)!=='{#id');
				}

				if ($bDoIt) {
					if (($width[$i]!=='*') || ($height[$i]!=='*')) {
						$wID+=1;

						// Add width / height for Pandoc conversion
						// See http://pandoc.org/MANUAL.html#extension-link_attributes
						$tmp='{#id'.$wID.' .class';

						if ($width[$i]!=='*') {
							$tmp.=' width='.$width[$i].'px';
						}

						if ($height[$i]!=='*') {
							$tmp.=' height='.$height[$i].'px';
						}

						$tmp.='}';

						$params['markdown']=str_replace($full[$i], $tag[$i].$tmp.$after[$i], $params['markdown']);

						$aeSession->set('img_id', $wID);
					}
				} // if ($bDoIt)
			} // for
		} // if (count($matches[0]) > 0)

		return true;
	}
}
