<?php
/**
 * Add search functionnality (only when the note is displayed through the
 * interface
 */

namespace MarkNotes\Plugins\Page\HTML;

defined('_MARKNOTES') or die('No direct access allowed');

class Search extends \MarkNotes\Plugins\Page\HTML\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.page.html.search';
	protected static $json_options = JSON_OPTIONS_TAGS;

	/**
	 * Provide additionnal javascript
	 */
	public static function addJS(&$js = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/search/';

		// For highligthing content in a note : after a search,
		// the displayed note will have the search term highlighted
		// @link https://github.com/knownasilya/jquery-highlight
		$script = "<script ".
			"src=\"".$url."libs/jquery-highlight/jquery.highlight.js\" ".
			"defer=\"defer\"></script>\n";

		// Used by the search box, for auto-completion
		// @link https://github.com/sergiodlopes/jquery-flexdatalist
		$script .= "<script ".
			"src=\"".$url."libs/jquery-flexdatalist/jquery.flexdatalist.min.js\" ".
			"defer=\"defer\"></script>\n";

		$script .= "<script src=\"".$url."search.js\" ".
			"defer=\"defer\"></script>\n";

		$script .=
			"<script>\n".
			"marknotes.settings.search_max_width=".SEARCH_MAX_LENGTH.";\n".
			"</script>\n";

		$js .= $aeFunctions->addJavascriptInline($script);

		return true;
	}

	/**
	 * Provide additionnal stylesheets
	 */
	public static function addCSS(&$css = null): bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$url = rtrim($aeFunctions->getCurrentURL(), '/');
		$url .= '/marknotes/plugins/page/html/search/';

		$script = "<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$url."libs/jquery-flexdatalist/jquery.flexdatalist.min.css\" />\n";
		$script .=
			"<link media=\"screen\" rel=\"stylesheet\" type=\"text/css\" ".
			"href=\"".$url."search.css\">\n";

		$css .= $aeFunctions->addStyleInline($script);

		return true;
	}

	/**
	 * Modify the HTML rendering of the note
	 */
	public static function doIt(&$html = null): bool
	{
		if (trim($html) === '') {
			return true;
		}

		if (preg_match("/\<\!--%SEARCH%--\>/", $html, $match)) {
			$aeSettings = \MarkNotes\Settings::getInstance();

			$placeHolder = $aeSettings->getText('search_placeholder', 'Search...');

			// No double quote...
			$placeHolder = str_replace('"', "'", $placeHolder);

			$sSearch='<div id="divSearch" class="search sidebar-form">
			  <div class="input-group">
				<input id="search" type="text" name="search" class="flexdatalist form-control" data-data="tags.json" data-search-in="name" data-min-lenght="3" placeholder="'.$placeHolder.'">
				<span class="input-group-btn">
					<button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
					</button>
				  </span>
			  </div>
			</div>';

/*
$sSearch='<input id="search" name="search" type="text" '.
'class="flexdatalist" placeholder="'.$placeHolder.'" '.
'maxlength="'.SEARCH_MAX_LENGTH.'" '.
'data-data="tags.json" data-search-in="name" data-min-lenght="3" />';
*/
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$sSearch = "\n<!-- Lines below are added by ".__FILE__."-->\n".
					trim($sSearch, "\n")."\n".
					"<!-- End for ".__FILE__."-->\n";
			}
			/*<!-- endbuild -->*/

			$html = str_replace($match, $sSearch, $html);
		}

		return true;
	}
}
