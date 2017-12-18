<?php
/**
 * Translate : receive a MD string and call an online translation
 * application like Google Translate.
 *
 * Answer to URL index.php?task=task.translate.run&content=....
 *
 * @Link https://github.com/Stichoza/google-translate-php/
 */
namespace MarkNotes\Plugins\Task\Translate;

defined('_MARKNOTES') or die('No direct access allowed');

use Stichoza\GoogleTranslate\TranslateClient;

class Run extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.translate';
	protected static $json_options = 'plugins.options.task.translate';

	/**
	 * Call Google API translation library
	 */
	private static function translate(string $content) : string
	{
		// Retrieve the language used for marknotes
		$aeSettings = \MarkNotes\Settings::getInstance();
		$arrSettings = $aeSettings->getPlugins('/regional');
		$language = trim($arrSettings['language'] ?? 'en');

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log("Translate into ".$language,"debug");
		}
		/*<!-- endbuild -->*/

		$libs = __DIR__.DS.'libs/google-translate-php/';

		require_once($libs.'Tokens/TokenProviderInterface.php');
		require_once($libs.'Tokens/GoogleTokenGenerator.php');
		require_once($libs.'Tokens/SampleTokenGenerator.php');
		require_once($libs.'TranslateClient.php');

		$tr = new TranslateClient();

		// Don't mention source language, autodetect
		$tr->setSource(null);

		// Translate into the language specified in
		// settings.json -> regional -> language
		$tr->setTarget($language);

		$tr->setUrlBase('http://translate.google.cn/translate_a/single');

		$return = $tr->translate($content);

		return $return;
	}

	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$content = trim($aeFunctions->getParam('content', 'unsafe',
			'', false));

		if ($content == '') {
			$content = 'ERROR - The translate task has been called '.
				'but no content has been provided. '.
				'That task requires a mandatory content parameter';

			/*<!-- build:debug -->*/
			$aeSettings = \MarkNotes\Settings::getInstance();
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log($content,"error");
			}
			/*<!-- endbuild -->*/
		} else {
			// Call html-to-markdown and make the conversion to MD
			$content = self::translate($content);
		}

		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo $content;

		die();
	}
}
