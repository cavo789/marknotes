<?php
/**
 * Handle note's encryption : display the encrypted content in a span with
 * a small lock icon
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Encrypt extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.encrypt';
	protected static $json_options = JSON_OPTIONS_ENCRYPT;

	/**
	 * Scan the $content and search for <encrypt> tags.
	 * If found, unencrypt the information
	 */
	public static function doIt(&$content = null) : bool
	{
		if (trim($content) === '') {
			return true;
		}

		$aeSession = \MarkNotes\Session::getInstance();

		// ----------------------------------------------------------------
		//
		// Add a three-stars icon (only for the display) to inform the
		// user about the encrypted feature

		// The encrypt markdown plugin use the ENCRYPT_MARKDOWN_TAG tag
		// between and after an encrypted portion (like in
		//   ENCRYPT_MARKDOWN_TAG<<<MyEncryptedPassword>>>>ENCRYPT_MARKDOWN_TAG
		$pattern = preg_quote(ENCRYPT_MARKDOWN_TAG).
			// ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
			'([\\S\\n\\r\\s]*?)'.
			preg_quote(ENCRYPT_MARKDOWN_TAG);

		$aeSession->remove('NoteContainsEncryptedData');

		if (preg_match_all('/'.$pattern.'/mi', $content, $matches)) {
			list($tag, $encrypted_portion) = $matches;

			$aeSession->set('NoteContainsEncryptedData', true);

			$aeSettings = \MarkNotes\Settings::getInstance();
			$text = $aeSettings->getText('encrypted_hint', 'This information is encrypted in the original file and decoded here for screen display');
			$text = str_replace('"', '\"', $text);

			$j = count($matches[0]);

			for ($i=0; $i < $j; $i++) {
				$icon_stars = '<i class="icon_encrypted fa fa-lock onlyscreen" '.
					'aria-hidden="true" data-encrypt="true" title="'.$text.'"></i>';

				$encrypted = $icon_stars.$encrypted_portion[$i].$icon_stars;

				// This isn't the edit mode : show the lock icon ($icon_stars)
				$content = str_replace($tag[$i], $encrypted, $content);
			} // for($i;$i<$j;$i++)
		} // if (count($matches[1])>0)

		return true;
	}
}
