<?php
/**
 * Add hyperlinks to specific words (tags) so the reader can jump
 * between notes
 */
namespace MarkNotes\Plugins\Content\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Tags extends \MarkNotes\Plugins\Content\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.content.html.tags';
	protected static $json_options = JSON_OPTIONS_TAGS;

	public static function doIt(&$content = null) : bool
	{
		// Can be empty when no note is being displayed
		if (trim($content) === '') {
			return true;
		}

		// Don't keep unwanted HTML tags
		$arrNotIn = self::getOptions('not_in_tags', array('a','abbr'));
		$aeRegex = \MarkNotes\Helpers\Regex::getInstance();
		$tmp = $aeRegex->removeTags($content, $arrNotIn);

		$aeSettings = \MarkNotes\Settings::getInstance();

		// Get the list of tags
		$arrTags = self::getOptions('keywords', array());

		// Retrieve the name of the class that will be added to the tag
		$class = self::getOptions('class', 'tag');

		$msg = $aeSettings->getText('apply_filter_tag', 'Display notes containing this tag', true);

		foreach ($arrTags as $tag) {
			// For each tag, try to find the word in the html string
			$regex = $aeRegex->notInsideATag(preg_quote($tag));

			if (preg_match_all($regex, $tmp, $matches)) {
				// Store the different parts

				// Considerer the following example :
				//
				//  <a hef="www.github.com">Look my github repo</a>
				//
				// $pattern will match the full string so
				//     '<a hef="www.github.com">Look my github repo</a>'
				// $before will match what is before the tag so
				//     '<a hef="www.github.com">Look my '
				// $tag will match what is before the matched tag so 'github'
				// $after will match what is after the tag so
				//     ' repo</a>'

				list($pattern, $before, $tag, $after) = $matches;

				for ($i=0; $i<count($matches[0]); $i++) {
					// Don't if tag is empty
					if (trim($tag[$i])!=='') {
						// Prepare the new value : put the tag inside a span
						$sText = '<span class="'.$class.'" title="'.$msg.'" '.
							'data-task="fnPluginContentTag" '.
							'data-param="'.$tag[$i].'">'.$tag[$i].'</span>';

						// Get back what was before and after the tag
						$sText = $before[$i].$sText.$after[$i];

						// And replace it in the HTML string
						$tmp = str_replace($pattern[$i], $sText, $tmp);

						$content = str_replace($pattern[$i], $sText, $content);
					}
				} // for ($i=0; $i<count($pattern); $i++)
			} // if (count($matches) > 0)
		} // foreach ($arrTags as $tag) {
		return true;
	}

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// This plugin is only needed when at least one tag is mentionned
			$arrOptions = self::getOptions('keywords', array());
			$bCanRun = (count($arrOptions) > 0);
		}

		return $bCanRun;
	}
}
