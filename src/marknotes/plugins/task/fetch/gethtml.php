<?php
/**
 * Use GuzzleHttp or CURL to retrieve the HTML content of a page
 *
 * Anwser to URL like the one below
 * index.php?task=task.fetch.gethtml&param=https://www.xxxxx
 */
namespace MarkNotes\Plugins\Task\Fetch;

defined('_MARKNOTES') or die('No direct access allowed');

class GetHTML extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.fetch';
	protected static $json_options = 'plugins.options.task.fetch';

	// Default user-agent if not specified in the settings.json,
	// in plugins->options->task->fetch->user-agent
	protected static $ua = 'Mozilla/5.0 (Windows NT 6.1; WOW64) '.
		'AppleWebKit/537.36 (KHTML, like Gecko) '.
		'Chrome/35.0.1916.153 Safari/537.36 FirePHP/4Chrome';

	/**
	 * Establish a CURL transaction and retrieve the HTML of
	 * the specified URL
	 */
	private static function getHTML(string $url) : string
	{
		// For the Joomla CMS for instance, the ?tmpl=component
		// parameter allow to only get the content without any
		// additionnals stuffs (modules, template, ...).
		$addQuerystring = trim(self::getOptions('querystring', ''));
		if ($addQuerystring !== '') {
			$url .= $addQuerystring;
		}

		// Timeout delay in seconds
		// plugins->options->task->fetch->timeout
		$timeout = self::getOptions('timeout', 5);
		if (trim($timeout) == '') {
			$timeout = 5;
		}

		$aeSettings = \MarkNotes\Settings::getInstance();
		$lib = $aeSettings->getFolderLibs().'GuzzleHttp'.DS;

		if (is_dir($lib)) {
			// Use GuzzleHttp
			$client = new \GuzzleHttp\Client(
				array('curl'=>array(CURLOPT_SSL_VERIFYPEER=>false))
			);

			$res = $client->request('GET', $url,
				['connect_timeout' => $timeout]);

			$content = $res->getBody();

		} else {
			// Use cURL

			// Get the User-agent to use
			$ua = self::getOptions('user-agent', static::$ua);
			if (trim($ua) == '') {
				$ua = static::$ua;
			}

			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_USERAGENT, $ua);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

			$content = curl_exec($ch);

			curl_close($ch);
		}

		return trim($content);
	}

	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = $aeFunctions->getParam('param', 'string', '', false);
		$url = trim($url);

		$sHTML = '';

		if ($url !== '') {
			$sHTML = self::getHTML($url);
		}

		if ($sHTML !== '') {
			// List of nodes that can be removed since are not
			// part of the content we want to keep

			$arrRemoveDOM = self::getOptions('remove_DOM', array());

			// List of attributes that can be removed from html
			// tags once the desired content is isolated
			$arrRemoveAttribs = self::getOptions('remove_Attributes', array());

			require_once('helpers/clean_html.php');

			$aeClean = new Helpers\CleanHTML($sHTML);

			$aeClean->setRemoveDOM($arrRemoveDOM);
			$aeClean->setRemoveAttributes($arrRemoveAttribs);

			$sHTML = $aeClean->doIt();

			unset($aeClean);
		}

		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo $sHTML;

		return true;
	}
}
