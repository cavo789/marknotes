<?php
/**
 * Return a robots.txt file
 *
 * It's possible to not allow an human to access to the robots.txt file thanks
 * to a setting in the settings.json file. To only allow bots (crawlers), you
 * can add this settings :
 *
 *		"plugins": {
 *		     "options": {
 *				"task": {
 *					"robots-txt": {
 *						"bots_only": 1
 *					}
 *				}
 *		     }
 *		 }
 *
 * When the access is allowed, the file /robots.txt will be read and
 * send to the browser. If that file doesn't exists but well /robots.txt.dist,
 * then that second file will be sent.
 */
namespace MarkNotes\Plugins\Task\RobotsTxt;

defined('_MARKNOTES') or die('No direct access allowed');

class Get extends \MarkNotes\Plugins\Task\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.task.robots-txt';
	protected static $json_options = 'plugins.options.task.robots-txt';

	public static function run(&$params = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$sReturn='';

		// When disallow_all is set, deny access to everyone (bots and humans)
		$bContinue = boolval(self::getOptions('disallow_all', 0)==0);

		if (!$bContinue) {
			$sReturn = "User-agent: *\nDisallow: /\n";
		}

		if ($bContinue) {
			// If bots_only is set to 0 (so, no, not only bots), ok,
			// we can continue even if it's an human
			$bContinue = boolval(self::getOptions('bots_only', 0)==0);
		}

		if (($sReturn==='') && (!$bContinue)) {
			// bots_only is set to 1 in the settings.json file
			// so restrict access and allow only bots; not humans

			// @link https://github.com/JayBizzle/Crawler-Detect
			$CrawlerDetect = new \Jaybizzle\CrawlerDetect\CrawlerDetect;

			// Check the user agent of the current 'visitor'
			if ($CrawlerDetect->isCrawler()) {
				// Ok, it's a crawler (i.e. a bot)
				$bContinue = true;
			} else {
				header('HTTP/1.0 403 Forbidden - (marknotes - Bots only)');
				$sReturn = $aeSettings->getText(
					'file_not_allowed',
					'Access denied - You are not allowed to access to this file'
				);
			}

			unset($CrawlerDetect);
		} // if (!$bContinue)

		if ($bContinue) {
			// Get the robots.txt content
			$fname = $aeSettings->getFolderWebRoot().'robots.txt';

			if (!is_file($fname)) {
				$fname = $aeSettings->getFolderWebRoot().'robots.txt.dist';
			}

			if (!is_file($fname)) {
				$fname = $aeSettings->getFolderAppRoot().'robots.txt.dist';
			}

			if (is_file($fname)) {
				// Ok, a robots.txt file has been found.
				$sReturn = file_get_contents($fname);

				// Get the /docs folder
				$docs = rtrim($aeSettings->getFolderDocs(false), DS);

				// And put it in the file
				$sReturn = str_replace('%DOCS%', $docs, $sReturn);

				// %URL% can be used in the robots.txt for, for instance,
				// points to the sitemap
				// Example : Sitemap: %URL%/sitemap.xml
				if (strpos($sReturn, '%URL%')!==false) {
					$aeFunctions = \MarkNotes\Functions::getInstance();
					$url = rtrim($aeFunctions->getCurrentURL(), '/');
					$sReturn = str_replace('%URL%', $url, $sReturn);
				}
			} else {
				header("HTTP/1.0 404 Not Found - (marknotes - robots.txt not found)");
			}
		} // if (!$bContinue)

		header("Content-Type: text/plain");
		header('Content-Transfer-Encoding: ascii; charset=iso-8859-1');
		header("cache-control: public, max-age=31536000");

		$offset = 365 * 24 * 60 * 60;  // 1 year
		$expire = "expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
		header($expire);

		echo $sReturn;

		return true;
	}
}
