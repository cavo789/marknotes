<?php
// @link https://github.com/thephpleague/html-to-markdown
// Remark : urls and images (i.e. all links) are lost by this converter
// so it's not fully working

//$url = 'https://www.joomla.org/announcements/general-news/5719-4-reasons-why-you-should-get-php-7.html';

$url = trim($_GET['url']);

if ($url=='') {
	echo '<h1>Help for the translation of Joomla.org content</h1>';
	echo '<p>Please mention the URL </p>';
	echo '<p>Example :  <strong>http://cavo789.com/test/index.php?url=https://www.joomla.org/announcements/general-news/5719-4-reasons-why-you-should-get-php-7.html</strong></p>';
	die();
} else {
	$url .= '?tmpl=component';
}

function getContent($url) {


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

	//

	$dom = new DOMDocument();
	@$dom->loadHTML($content);
	$xpath = new DOMXPath($dom);

	// The article is within the <div class="item-page">content</div>
	$div = $xpath->query('//div[@class="item-page"]');
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

	return $content;

}

require_once('ConfigurationAwareInterface.php');
require_once('Converter/ConverterInterface.php');

$dir = glob('Converter/*.php');
foreach ($dir as $file) {
	require_once($file);
}

require_once('Environment.php');
require_once('ElementInterface.php');
require_once('Element.php');
require_once('Configuration.php');
require_once('HtmlConverter.php');

use League\HTMLToMarkdown\HtmlConverter;

$converter = new HtmlConverter();

$converter->getConfig()->setOption('header_style', 'atx');
$converter->getConfig()->setOption('italic_style', '*');
$converter->getConfig()->setOption('bold_style', '**');
$converter->getConfig()->setOption('strip_tags', false);

$html = getContent($url);

$markdown = $converter->convert($html);

echo '<pre>'.$markdown.'</pre>';
