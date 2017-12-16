<?php
/**
 * Use CURL to retrieve the HTML content of a page
 *
 * Anwser to URL like the one below (names are base64_encoded)
 * index.php?task=task.fetch.gethtml&param=
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

	private static function getHTML(string $url) : string
	{
		// Get the User-agent to use
		$ua = self::getOptions('user-agent', static::$ua);
		if (trim($ua) == '') {
			$ua = static::$ua;
		}

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

		return trim($content);
	}

	private static function cleanHTML(string $html) : string
	{
		// To retrieve only the desired content from the HTML,
		// we need "selectors" : do we need to take the body,
		// a specific div, an article node, ...
		// The Selector entry is there for this purpose
		$arrSelector = self::getOptions('selector', array("//body"));

		// When we've the content, we can probably make a few
		// cleaning like removing social networks f.i.
		// The Remove entry is there for this.
		$arrRemove = self::getOptions('remove', array());

		$dom = new \DOMDocument();
		$dom->validateOnParse = false;
		@$dom->loadHTML($html);
		$xpath = new \DOMXPath($dom);

		$content = '';

		foreach ($arrSelector as $selector) {
			$div = $xpath->query($selector);
			$div = $div->item(0);
			$tmp = $dom->saveXML($div);

			// Do we've found that selector ? (f.i. //body)
			if ($tmp !== '')  {
				// Yes => great, we can remember that part
				$content = $tmp;
				@$dom->loadHTML($content);
				$xpath = new \DOMXPath($dom);
			}
		} // foreach()

		// We don't need the doctype; just the html of the article
		$regex = '~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i';
		$content = preg_replace($regex, '', $content);

		if (trim($content) !== '') {
			foreach ($arrRemove as $selector) {
				$div = $xpath->query($selector);
				$div = $div->item(0);
				$tmp = $dom->saveXML($div);

				// Do we've found that selector ?
				if ($tmp !== '') {
					// Yes => it means that, since we're in the
					// remove selector, that part should be removed
					// from the content
					$content = str_replace($tmp, '', $content);
				}
			} // foreach()
		}

		// Don't keep inline script
		$regex = '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i';
		$content = preg_replace($regex, '', $content);

		// Don't keep the xml tag
		$regex = '~<\?xml[^>]*>\s*~i';
		$content = preg_replace($regex, '', $content);

		// Don't keep HTML comments
		$content = preg_replace('/<!--(.*)-->/Uis', '', $content);

		// Carriage return
		$content = preg_replace('/&#13;/Uis', '', $content);

		// Remove spaces between tags (not inside tags)
		$content = preg_replace('/(\>)\s*(\<)/m', '$1$2', $content);

		// From here, $content contains only the article's content
		// We can send it back to the requestor

		return $content;
	}

	public static function run(&$params = null) : bool
	{
		$aeFunctions = \MarkNotes\Functions::getInstance();

		$url = $aeFunctions->getParam('param', 'string', '', false);
		$url = trim($url);

		$sHTML = self::getHTML($url);

		if ($sHTML !== '') {
			$sHTML = self::cleanHTML($sHTML);
		}

		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Transfer-Encoding: ascii');
		echo $sHTML;

		return true;
	}
}
