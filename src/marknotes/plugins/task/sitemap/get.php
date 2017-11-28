<?php
/**
 * Display the sitemap
 */

namespace MarkNotes\Plugins\Task\Sitemap;

defined('_MARKNOTES') or die('No direct access allowed');

class Get
{
	private static function getFiles() : array
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();

		// Call the ACLs plugin
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->loadPlugins('task.acls.load');
		$args=array();
		$aeEvents->trigger('task.acls.load::run', $args);

		// $bACLsLoaded will be set true if at least one folder is protected
		$bACLsLoaded = boolval($aeSession->get('acls', '') != '');

		// Retrieve the list of files
		$docs=$aeSettings->getFolderDocs(true);
		$arrFiles = $aeFunctions->array_iunique($aeFiles->rglob('*.md', $docs));

		if (count($arrFiles) !== 0) {
			// Are there protected folders i.e. only accessible for
			// allowed users ?

			if ($bACLsLoaded) {
				// Run the filter_list task to remove any protected files
				// not allowed for the current user

				$aeEvents->loadPlugins('task.acls.filter_list');
				$args=array(&$arrFiles);
				$aeEvents->trigger('task.acls.filter_list::run', $args);

				// Retrieve the filtered array i.e. that Files
				// well accessible to the current user
				$arrFiles=$args[0];
			} // if ($bACLsLoaded)

			natcasesort($arrFiles);

			// Be carefull, folders / filenames perhaps contains accentuated characters
			$arrFiles = array_map('utf8_encode', $arrFiles);
		} // if (count($arrFiles)==0)

		return $arrFiles;
	}

	public static function run(&$params = null)
	{
		$aeFiles = \MarkNotes\Files::getInstance();
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$aeSession = \MarkNotes\Session::getInstance();
		$aeSettings = \MarkNotes\Settings::getInstance();

		$arrOptimize = $aeSettings->getPlugins(JSON_OPTIONS_OPTIMIZE);

		$bOptimize = $arrOptimize['server_session'] ?? false;

		$xml='';

		if ($bOptimize) {
			// Get the list of files/folders from the session object if possible
			$xml = trim(json_decode($aeSession->get('Sitemap', '')));
		}

		if ($xml === '') {
			$arrFiles = self::getFiles();

			$xml = '';

			$folder = str_replace('/', DS, $aeSettings->getFolderDocs(true));

			foreach ($arrFiles as $file) {
				$relFileName = str_replace($folder, '', $file);

				$url = rtrim($aeFunctions->getCurrentURL(), '/').'/'.rtrim($aeSettings->getFolderDocs(false), DS).'/';
				$urlHTML = $url.str_replace(DS, '/', $aeFiles->replaceExtension($relFileName, 'html'));

				if (!$aeFiles->fileExists($file)) {
					$file = utf8_decode($file);
				}

				// filemtime will raise an error when the filename contains f.i. accentuated characters
				$lastModified = @filemtime($file);
				if ($lastModified == null) {
					$lastModified = filemtime(utf8_decode($file));
				}

				$xml .=
					'      <url>'.PHP_EOL.
					'         <loc>'.str_replace(' ', '%20', htmlspecialchars($urlHTML, ENT_HTML5)).'</loc>'.PHP_EOL.
					'         <lastmod>'.date('Y-m-d\TH:i:sP', $lastModified).'</lastmod>'.PHP_EOL.
					'         <changefreq>weekly</changefreq>'.PHP_EOL.
					'         <priority>1.0</priority>'.PHP_EOL.
					'      </url>'.PHP_EOL;
			} // foreach

			$xml =
				'<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
				'<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '.
					'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" '.
					'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
				'   '.$xml.PHP_EOL.
				'</urlset>';

			if ($bOptimize) {
				// Store the XML into the session object
				$aeSession->set('Sitemap', $xml);
			}
		} // if ($xml==='')

		// Nothing should be returned, the xml can be displayed immediatly
		header('Content-Type: application/xml; charset=utf-8');
		echo $xml;

		return true;
	}

	/**
	 * Attach the function and responds to events
	 */
	public function bind(string $task)
	{
		$aeEvents = \MarkNotes\Events::getInstance();
		$aeEvents->bind('run', __CLASS__.'::run', $task);
		return true;
	}
}
