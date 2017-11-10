<?php
/**
 * Utilities to work with regex
 */
namespace MarkNotes\Helpers;

defined('_MARKNOTES') or die('No direct access allowed');

class Regex
{
	protected static $hInstance = null;

	public function __construct()
	{
		return true;
	}

	public static function getInstance()
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Regex();
		}

		return self::$hInstance;
	}

	/**
	 * Remove mentionned tags from the content; tag and content.
	 * Example :
	 *
	 *    $html = '<p>test<a href='...'>LINK</a><pre>PRE</pre> string</p>';
	 *
	 *    // Remove the link and the pre tag so keep only "test string"
	 *    $tmp = removeTags($html, array('a', 'pre'));
	 */
	public static function removeTags(string $content, array $arrTags) : string
	{
		if ($arrTags !== array()) {
			foreach ($arrTags as $tag) {
				$content = preg_replace('#<'.$tag.'.*?>.*?<\/'.$tag.'>#is', '', $content);
			}
		}

		return $content;
	}

	/**
	 * In markdown, the sentences below is a <code> block. This function will
	 * remove it from the content
	 *
	 *    ```
	 *		something
	 *    ```
	 */

	public static function removeMarkdownCodeBlock(string $markdown) : string
	{
		return preg_replace('/`{3,}[^`]*`{3,}/', '', $markdown);
	}

	/**
	 * Add negative lookbehind to make sure that the search
	 * term (i.e. $word) isn't inside a HTML tag.
	 *
	 * In the example below, the word "marknotes" is twice in
	 * the img tag, in the src and alt attributes. If we don't want
	 * to match these occurence, this function will help a lot.
	 *
	 * Example :
	 * <p><img src="marknotes.svg" alt="logo-marknotes" />marknotes</p>
	 *
	 * notInsideATag('marknotes') will construct the regex for matching
	 * only the third term which is outside a tag.
	 */
	public static function notInsideATag(string $word) : string
	{
		// negative look behind to check that the matched string
		// is not included in a tag (i.e. between < and >)
		//   ==> skip the word if inside a HTML attribute
		return
			'/(<.+?>[^<>]*?)'.
			// Match the tag
			'('.$word.')'.
			// Get what is after the tag
			'([^<>]*?<.+?>)/i';
	}
}
