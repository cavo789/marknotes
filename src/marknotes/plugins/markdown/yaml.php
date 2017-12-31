<?php
/**
 * Process the markdown content; extract his YAML header.
 * Add the YAML header in the Session object.
 * If the header is not present, if the setting is "add_if_missing=1",
 * then create a header.
 *
 * The YAML block should, always, be at the top of the document
 * starting with '---' on the first line of the document and
 * ending with '---' on an another line
 *
 * For instance :
 *
 * ---
 * title: An amazing YAML block
 * author: Christophe Avonture
 * company: marknotes
 * date: tuesday 1st january 2018
 * ---
 * Note content
 * ....
 */

namespace MarkNotes\Plugins\Markdown;

use \Symfony\Component\Yaml\Yaml;

defined('_MARKNOTES') or die('No direct access allowed');

class YAMLHeader extends \MarkNotes\Plugins\Markdown\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.markdown.yaml';
	protected static $json_options = 'plugins.options.markdown.yaml';

	/**
	 * When the note doesn't contains yet a YAML header, add one
	 */
	private static function BuildYAML(string &$md) : array
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Retrieve the page title i.e. the first heading 1.
		$aeMarkDown = \MarkNotes\FileType\Markdown::getInstance();
		$pageTitle = $aeMarkDown->getHeadingText($md, '#');

		$arr=array();

		// Check if there are default values and if yes, add them
		$defaults = self::getOptions('defaults', array());

		if (count($defaults)>0) {
			foreach ($defaults as $key => $value) {
				switch ($value) {
					case '%TITLE%':
						$value=trim($pageTitle);
						break;
					case '%TODAY%':
						$datetime = new \DateTime();
						// International format
						$value=$datetime->format('Y\-m\-d\ H:i:s');
						break;
					case '%LANGUAGE%':
						$value=$aeSettings->getLanguage();
						break;
				} // switch

				$arr[$key] = $value;
			} // foreach
		} // if (count($defaults)>0)

		return $arr;
	}

	public static function readMD(array &$params = array()) : bool
	{
		if (trim($params['markdown']) === '') {
			return true;
		}

		$yaml = '';

		// Get the separator used for the YAML block
		$yaml_separator = self::getOptions('yaml_separator', '---');

		$aeFiles = \MarkNotes\Files::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		// Only if the library is installed otherwise nothing to do
		$lib = $aeSettings->getFolderLibs()."symfony/yaml/Yaml.php";
		if ($aeFiles->exists($lib)) {
			// Load the library
			include_once $lib;

			$quote = function ($str) {
				return preg_quote($str, "~");
			};

			$regex = '~^('
				// $matches[1] start separator
				.implode('|', array_map($quote, array($yaml_separator)))
				 // $matches[2] between separators i.e. the YAML content
				."){1}[\r\n|\n]*(.*?)[\r\n|\n]+("
				// $matches[3] end separator
				.implode('|', array_map($quote, array($yaml_separator)))
				// $matches[4] the document content i.e. the markdown content
				."){1}[\r\n|\n]*(.*)$~s";

			$md = $params['markdown'];

			if (preg_match($regex, $md, $matches) === 1) {
				// Yes, a YAML header has been found
				// Get the matches parts
				list ($pattern, $tag_before, $yaml, $tag_after, $content) = $matches;

				$params['yaml'] = Yaml::parse(trim($yaml));
				$params['markdown'] = trim($content);
			} else {
				// There is no YAML block yet

				// Retrieve the title for the section, from settings.json
				$arrSettings = $aeSettings->getPlugins('options.yaml');

				// Check if we can add the block automatically
				$add = boolval(self::getOptions('add_if_missing', 0));

				if ($add) {
					$params['yaml'] = self::BuildYAML($md);

					// Remember the note's YAML header
					$yaml = YAML::dump($params['yaml']);
				} // if ($add)
			}
		} else { // if ($aeFiles->exists($lib))
			// The YAML library isn't found
		}

		$aeSession = \MarkNotes\Session::getInstance();
		$aeSession->set('yaml', $yaml);
		return true;
	}
}
