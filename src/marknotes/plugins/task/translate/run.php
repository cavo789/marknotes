<?php
/**
 * Translate
 *
 * @Link https://github.com/Stichoza/google-translate-php/tree/master/tests
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
	 * Call the html-to-markdown library and make the conversion
	 * https://github.com/thephpleague/html-to-markdown
	 */
	private static function translate(string $content) : string
	{

		// Retrieve the language used for marknotes
		$aeSettings = \MarkNotes\Settings::getInstance();
		$arrSettings = $aeSettings->getPlugins('/regional');
		$language = trim($arrSettings['language'] ?? 'en');

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


		if (is_file($filename = __DIR__.DS.'test.html')) {
	$content = utf8_encode(file_get_contents($filename));
} else {
			$content = trim($aeFunctions->getParam('param', 'unsafe', '', false));
}

		// Call html-to-markdown and make the conversion to MD
		$content = self::translate($content);

		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo $content;

		return true;
	}
}
