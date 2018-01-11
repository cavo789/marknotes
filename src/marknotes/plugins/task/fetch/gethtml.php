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

		$aeFolders = \MarkNotes\Folders::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
			$aeDebug->log("Fetch URL : ".$url,"debug");
		}
		/*<!-- endbuild -->*/

		// Timeout delay in seconds
		// plugins->options->task->fetch->timeout
		$timeout = self::getOptions('timeout', 5);
		if (trim($timeout) == '') {
			$timeout = 5;
		}

		$lib = $aeSettings->getFolderLibs().'GuzzleHttp'.DS;

		if($aeFolders->exists($lib)) {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log("Fetch using GuzzleHttp","debug");
			}
			/*<!-- endbuild -->*/

			// Use GuzzleHttp
			// Don't verify SSL to prevent errors on localhost
			// Error can be "SSL certificate problem: unable to get local issuer certificate"
			$client = new \GuzzleHttp\Client(['verify'=>false]);

			$res = $client->request('GET', $url,
				['connect_timeout' => $timeout]);

			$content = $res->getBody();
		} else {
			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug->log("Fetch using cURL","debug");
			}
			/*<!-- endbuild -->*/

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

	private static function miscUpdate(string $sHTML) : string {
		// Replace a few characters because we'll be displayed
		// as a "?" in the HTML content
		$arr = array(
			array('’', "'"),
			array('“', '"'),
			array('”', '"'),
			array('–', '-'),
			array('…', '...')
		);

		foreach ($arr as $char) {
			$sHTML = str_replace($char[0], $char[1], $sHTML);
		}

		// Sometimes the <a> is immediatly after a character
		// like (click here<a> link </a>to ...) and it's not really
		// correct (syntax) and won't be great once converted into
		// markdown so add a space before and after an obtain :
		// like (click here <a>link</a> to ...)

		// text<a> ==> text <a>
		$regex = '~([^\s])(<a[^>]*>)~im';
		$sHTML = preg_replace($regex, "$1 $2", $sHTML);

		// </a>text ==> </a> text
		$regex = '~(<\/a>)([^\s])~im';
		$sHTML = preg_replace($regex, "$1 $2", $sHTML);

		// -----------------------
		// <p and <div should be on new line so the Markdown
		// conversion will give a better result
		// 1. <p> (on a new line) will be change to have two LF
		$regex = '~\n(<[div|p][^>]*>)~im';
		$sHTML = preg_replace($regex, "\n\n$1", $sHTML);
		// 2.  someting<p>  ==> something followed by two LF <p>
		$regex = '~([^\n])(<[div|p][^>]*>)~im';
		$sHTML = preg_replace($regex, "$1\n\n$2", $sHTML);
		// -----------------------

		// Add <p> ... </p> if the line doesn't contains them
		// and contains f.i. an anchor.
		// Convert <a>....</a> on a single line to
		// <p><a>....</a></p>
		// REGEX : DON'T USE $ BUT WELL \n? (otherwise it won't work)
		$regex = '~^(<a[^>]*>.*<\/a>\n?)~im';
		$sHTML = preg_replace($regex, "<p>$1</p>", $sHTML);

		return $sHTML;
	}

	/**
	 * If the user tries to access to an URL, the PHP wrapper
	 * should well be installed. If not, show an error and stop.
	 */
	private static function checkProtocol(string $url) : bool
	{
		$bContinue = false;

		// Get the list of wrappers loaded i.e. the capacity for PHP
		// to retrieve HTTP, HTTPS, ... content
		$w = stream_get_wrappers();

		// Get the protocol of the URL we need to visit :
		// http or https?
		$protocol = substr($url, 0, 5);

		if (strcasecmp($protocol, 'https') == 0) {
			// https : check if the https wrapper is installed
			$bContinue = in_array('https', $w);

			//throw new \Exception("HTTPS wrapper not installed");
			if (!$bContinue) {
				echo '<h2>Marknotes - Error</h2>';
				echo '<strong>The https wrapper is not installed. '.
					'If you can, please enable it in your apache '.
					'configuration otherwise you won\'t be able '.
					'to use https URLs (probably http:// well).'.
					'</strong>';
			}
		} else {
			$bContinue = in_array('http', $w);
			//throw new \Exception("HTTP wrapper not installed");
			if (!$bContinue) {
				echo '<h2>Marknotes - Error</h2>';
				echo '<strong>The http wrapper is not installed. '.
					'If you can, please enable it in your apache '.
					'configuration otherwise you won\'t be able '.
					'to use http URLs</strong>';
			}
		}

		if (!$bContinue) {
			echo '<p>You tried to access to : '.$url.'</p>';
		}

		return $bContinue;
	}

	/**
	 * Run the task
	 */
	public static function run(&$params = null) : bool
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		/*<!-- build:debug -->*/
		if ($aeSettings->getDebugMode()) {
			$aeDebug = \MarkNotes\Debug::getInstance();
		}
		/*<!-- endbuild -->*/

		// Note being edited
		$filename = $aeFunctions->getParam('param', 'string', '', true);
		$filename = json_decode(urldecode($filename));
		$filename = $aeFiles->removeExtension($filename).'.md';

		// Derive the fullname
		$doc = $aeSettings->getFolderDocs(true);
		$fullname = str_replace('/', DS, ($doc.ltrim($filename, DS)));

		$url = $aeFunctions->getParam('url', 'string', '', false);
		$url = trim($url);

		$sHTML = '';

		if ($url=='') {
			$sHTML = 'ERROR - The fetch task has been called but '.
				'no URL has been provided. That task requires '.
				'a mandatory url parameter';

			/*<!-- build:debug -->*/
			if ($aeSettings->getDebugMode()) {
				$aeDebug = \MarkNotes\Debug::getInstance();
				$aeDebug->log($sHTML,"error");
			}
			/*<!-- endbuild -->*/
		} else {

			// Check if we can continue
			if (!self::checkProtocol($url)) {
				die();
			}

			// A temporary filename will be created in the /tmp folder
			// The name will be the URL ressources and be sure it will
			// be correct so use the slugify() function.
			// f.i. C:\site\tmp\fetched_5667-joomla-3-6-2-released.html
			// That file is created for debugging purposes but also
			// for cache objectives : don't access multiple times to
			// that URL if already processed once.
			$ftemp = $aeSettings->getFolderTmp().'fetched_'.
				$aeFunctions->slugify(basename($url)).'.html';

			// Reuse the cache
			if ($aeFiles->exists($ftemp)) {
				/*<!-- build:debug -->*/
				if ($aeSettings->getDebugMode()) {
					$aeDebug->log("Since that URL was already ".
						"fetched, reuse the cache, retrieve content ".
						"from ".$ftemp,"debug");
				}
				/*<!-- endbuild -->*/
				$sHTML = trim($aeFiles->getContent(utf8_decode($ftemp)));
			}

			if ($sHTML == '') {
				$sHTML = self::getHTML($url);
			}

			if ($sHTML !== '') {

				$sHTML = self::miscUpdate($sHTML);

				// Remember the retrieved content
				// This for debugging purposes and for
				// cache optimization
				try {
					$aeFiles = \MarkNotes\Files::getInstance();
					$aeFiles->create($ftemp, $sHTML);
				//	$aeFiles->fwriteUTF8BOM($ftemp, $sHTML);
				} catch (\Exception $e) {
				}

				// List of nodes where the content is placed.
				// That list will allow to faster retrieved desired
				// content and not pollute content by additionnal
				// elements like comments, navigation, ...
				$arrContentDOM = self::getOptions('content_DOM',
					array());

				// List of nodes that can be removed since are not
				// part of the content we want to keep
				$arrRemoveDOM = self::getOptions('remove_DOM', array());

				// List of attributes that can be removed from html
				// tags once the desired content is isolated
				$arrRemoveAttribs = self::getOptions('remove_Attributes',
					array());

				// The regex entry of plugins->options->task->fetch
				// contains search&replace expression for the content
				// f.i. Search a specific content and replace it by
				// a new value
				$arrRegex = self::getOptions('regex',
					array());

				require_once('helpers/clean_html.php');

				$aeClean = new Helpers\CleanHTML($sHTML, $url);

				$aeClean->setContentDOM($arrContentDOM);
				$aeClean->setRemoveDOM($arrRemoveDOM);
				$aeClean->setRemoveAttributes($arrRemoveAttribs);
				$aeClean->setRegex($arrRegex);

				$sHTML = $aeClean->doIt();

				unset($aeClean);

				$yaml = "original_url: ".$url;

				$aeSession = \MarkNotes\Session::getInstance();
				$aeSession->set('yaml', $yaml);

				// Rewrite the file on the disk
				$aeEvents = \MarkNotes\Events::getInstance();
				$aeEvents->loadPlugins('task.markdown.write');
				$params = array('markdown'=>$yaml.$sHTML);
				$args = array(&$params);
				$aeEvents->trigger('task.markdown.write::run', $args);

				// Remember the HTML after cleaning
				// for debugging purposes only (not used at all)
				$ftemp = $aeSettings->getFolderTmp().'cleaned_'.
					$aeFunctions->slugify(basename($url)).'.html';
				try {
					$aeFiles = \MarkNotes\Files::getInstance();
					$aeFiles->create($ftemp, $sHTML);
				} catch (\Exception $e) {
				}
			}
		}

		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo $sHTML;

		return true;
	}
}
