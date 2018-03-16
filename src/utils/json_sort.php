<?php
/**
 * By adding new translations variables and text in the
 * /languages/xxx.json file, files are no more ordered on the
 * variables
 *
 * This script will load .json files under /languages and will
 * rewrite these files after a ksort() function.
 *
 * So will order files like this :

 *		"action_download": "Download the file",
 *		"action_html": "View this note like a article",
 *		"action_prohibited": "... prohibits this action",
 *		"action_slideshow": "View this note like a slideshow",
 *
 */

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('DEFAULT_TIMEZONE', 'Europe/London');
define('DEFAULT_LOCALE', 'en-UK');

	// Correctly set the date so the last_update info will be
	// correct (UTC 0)
	setlocale(LC_ALL, DEFAULT_LOCALE);
	date_default_timezone_set(DEFAULT_TIMEZONE);

	$root = dirname(__DIR__);

	$arr = glob($root.DS.'languages/*.json');

	// Display an information
	echo '<h1>Sort JSON on key-value</h1>';

	foreach ($arr as $file) {
		$file = str_replace('/', DS, $file);

		if (is_file($file)) {
			$json = json_decode(file_get_contents($file), true);
			ksort($json);

			if (isset($json['@metadata'])) {
				$json['@metadata']['last-updated'] = date("Y-m-d H:i:s");
			}

			$fp = fopen($file, 'w');

			$sNew = json_encode($json, JSON_PRETTY_PRINT);

			fwrite($fp, $sNew);
			fclose($fp);

			// Display an information
			echo '<blockquote><p>'.$file.' has been rewriten with '.
				'the content below. Keys have been ordered correctly '.
				'and last-updated date/time updated.</p></blockquote>';

			// Display the new content
			//header('Content-Type: application/json');
			echo '<pre>'.json_encode($json, JSON_PRETTY_PRINT).'</pre>';
			echo '<hr/>';
		} // if (is_file($file))
	} // foreach ($arr as $file)

	echo '<p>Done</p>';
