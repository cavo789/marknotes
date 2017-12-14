<?php
/**
 * Use CURL to retrieve the HTML content of a page
 *
 * Anwser to URL like the one below (names are base64_encoded)
 * index.php?task=task.fetch.gethtml&param=
 */
namespace MarkNotes\Plugins\Task\Fetch;

defined('_MARKNOTES') or die('No direct access allowed');

use League\HTMLToMarkdown\HtmlConverter;

class GetHTML extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = ''; //'plugins.task.fetch';
	protected static $json_options = '';

	private static function getHTML(string $url) : string
	{
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) '.
			'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 '.
			'Safari/537.36 FirePHP/4Chrome');

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // Timeout delay in seconds
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

		$content = curl_exec($ch);

		curl_close($ch);

		$dom = new \DOMDocument();
		@$dom->loadHTML($content);
		$xpath = new \DOMXPath($dom);

		// The article is within the <div class="item-page">content</div>
		$selector = '//body';

		//$selector = '//article[@id="CONTENT"]';
		//REMOVE ALL <script> and <style>
		//$selector = '//div[@class="item-page"]';
		$div = $xpath->query($selector);
		$div = $div->item(0);
		$content = $dom->saveXML($div);

		// In the Joomla content, there is a lot of unneeded parts like
		// social network links, written by, ... Remove them
		$div = $xpath->query('//div[@class="share-container"]');
		$div = $div->item(0);
		$share = $dom->saveXML($div);
		$content = str_replace($share, '', $content);

		$div = $xpath->query('//dl[@class="article-info muted"]');
		$div = $div->item(0);
		$writtenBy = $dom->saveXML($div);
		$content = str_replace($writtenBy, '', $content);

		$div = $xpath->query('//ul[@class="pager pagenav"]');
		$div = $div->item(0);
		$navigation = $dom->saveXML($div);
		$content = str_replace($navigation, '', $content);

		return '<h1>Test from '.__FILE__.'</h1>'.
			'<p>Get from '.$url.'</p>'.
			'<hr/>'.
			$content;
	}

	private static function HTML2MD(string $html) : string
	{
		require_once(__DIR__.'/htmltomd/ConfigurationAwareInterface.php');
		require_once(__DIR__.'/htmltomd/Converter/ConverterInterface.php');

		$dir = glob(__DIR__.'/htmltomd/Converter/*.php');
		foreach ($dir as $file) {
			require_once($file);
		}

		require_once(__DIR__.'/htmltomd/Environment.php');
		require_once(__DIR__.'/htmltomd/ElementInterface.php');
		require_once(__DIR__.'/htmltomd/Element.php');
		require_once(__DIR__.'/htmltomd/Configuration.php');
		require_once(__DIR__.'/htmltomd/HtmlConverter.php');

		$converter = new HtmlConverter();

		$converter->getConfig()->setOption('header_style', 'atx');
		$converter->getConfig()->setOption('italic_style', '*');
		$converter->getConfig()->setOption('bold_style', '**');
		$converter->getConfig()->setOption('strip_tags', false);

		//$html = getContent($url);

		$markdown = $converter->convert($html);

		return $markdown;
	}

	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		//$url = trim(urldecode($aeFunctions->getParam('param', 'string', '', true)));

		$url='https://www.marknotes.fr/docs/marknotes/'.
			'Plugins/content/html/microdata.html';

		$sHTML = self::HTML2MD(self::getHTML($url));

		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo $sHTML;

die();
		return true;
	}
}
