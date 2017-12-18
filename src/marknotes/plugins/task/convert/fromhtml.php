<?php
/**
 * Convert HTML to markdown
 * Answer to the url index.php?task=task.convert.fromhtml&content=....
 *
 * @Link https://github.com/thephpleague/html-to-markdown
 */
namespace MarkNotes\Plugins\Task\Convert;

defined('_MARKNOTES') or die('No direct access allowed');

use League\HTMLToMarkdown\HtmlConverter;

class FromHTML extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.convert';
	protected static $json_options = 'plugins.options.task.convert';

	/**
	 * Call the html-to-markdown library and make the conversion
	 * https://github.com/thephpleague/html-to-markdown
	 */
	private static function convert(string $HTML) : string
	{
		$libs = str_replace('/', DS, __DIR__.'/libs/html2md/');

		/*<!-- build:debug -->*/
		$aeSettings = \MarkNotes\Settings::getInstance();
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log("Call html-to-markdown library","debug");
		}
		/*<!-- endbuild -->*/

		require_once($libs.'ConfigurationAwareInterface.php');
		require_once($libs.'Converter/ConverterInterface.php');

		$arr = glob($libs.'Converter/*.php');
		foreach ($arr as $file) {
			require_once($file);
		}

		$arr = array('Environment', 'ElementInterface',
			'Element', 'Configuration', 'HtmlConverter');

		foreach ($arr as $file) {
			require_once($libs.$file.'.php');
		}

		$converter = new HtmlConverter();

		$converter->getConfig()->setOption('header_style', 'atx');
		$converter->getConfig()->setOption('italic_style', '*');
		$converter->getConfig()->setOption('bold_style', '**');
		$converter->getConfig()->setOption('strip_tags', true);

		$markdown = trim($converter->convert($HTML));

		return $markdown;
	}

	/**
	 * Misc updates
	 */
	private static function miscUpdate(string $markdown) : string {

		// No need to have more than three linefeed (no empty lines)
		$markdown=preg_replace('/\n{3,}/si', "\n\n", $markdown);
		// and no need to have a line with space characters
		$markdown=preg_replace('/\n {1,}\n/si', "\n\n", $markdown);

		return $markdown;
	}

	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$sHTML = trim($aeFunctions->getParam('content', 'unsafe',
			'', false));

		if ($sHTML == '') {
			$sMD = 'ERROR - The convert task has been called but '.
				'no content has been provided. That task requires '.
				'a mandatory content parameter';

			/*<!-- build:debug -->*/
			$aeSettings = \MarkNotes\Settings::getInstance();
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log($sMD,"error");
			}
			/*<!-- endbuild -->*/
		} else {
			// Call html-to-markdown and make the conversion to MD
			$sMD = self::convert($sHTML);

			// Remove tags not proessed by html-to-markdown
			// The returned markdown string should no more contains
			// html tags
			$sMD = trim(strip_tags($sMD));

			$sMD = self::miscUpdate($sMD);

		}

		// Return the string
		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo $sMD;

		die();

	}
}
